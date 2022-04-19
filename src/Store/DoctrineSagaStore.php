<?php

declare(strict_types=1);

namespace Brzuchal\Saga\Store;

use Brzuchal\Saga\Association\AssociationValue;
use Brzuchal\Saga\Association\AssociationValues;
use Brzuchal\Saga\SagaInstanceNotFound;
use Brzuchal\Saga\SagaState;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

use function sprintf;

final class DoctrineSagaStore implements SagaStore, SetupableSagaStore
{
    public const DEFAULT_ASSOC_TABLE_NAME = 'saga_assoc';
    public const DEFAULT_DATA_TABLE_NAME = 'saga_data';

    public function __construct(
        protected Connection $connection,
        protected string $assocTableName = self::DEFAULT_ASSOC_TABLE_NAME,
        protected string $dataTableName = self::DEFAULT_DATA_TABLE_NAME,
        protected SerializerInterface $serializer = new Serializer([
            new DateTimeNormalizer([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i:s']),
            new ObjectNormalizer(
                propertyTypeExtractor: new ReflectionExtractor(),
                nameConverter: new CamelCaseToSnakeCaseNameConverter(),
            ),
        ], [
            new JsonEncoder(),
        ]),
    ) {
    }

    /**
     * @throws Exception
     *
     * @inheritdoc
     */
    public function findSagas(string $type, AssociationValue $associationValue): iterable
    {
        return $this->connection
            ->prepare(sprintf(
                'SELECT saga_id FROM %s WHERE association_key = ? AND association_value = ? AND saga_type = ?',
                $this->assocTableName,
            ))
            ->executeQuery([
                $associationValue->key,
                $associationValue->value,
                $type,
            ])
            ->fetchFirstColumn();
    }

    /**
     * @throws Exception
     * @throws SagaInstanceNotFound
     */
    public function loadSaga(string $type, string $identifier, object|null $object = null): SagaStoreEntry
    {
        $entry = $this->connection
            ->prepare(sprintf(
                'SELECT serialized, type, state FROM %s WHERE id = ?',
                $this->dataTableName,
            ))
            ->executeQuery([$identifier])
            ->fetchAssociative();

        if ($entry === false) {
            throw SagaInstanceNotFound::unableToLoad($type, $identifier);
        }

        return new SimpleSagaStoreEntry(
            $this->serializer->deserialize(
                $entry['serialized'],
                $entry['type'],
                'json',
                [ObjectNormalizer::OBJECT_TO_POPULATE => $object],
            ),
            $this->readAssociationValues($identifier),
            SagaState::from($entry['state']),
        );
    }

    /**
     * @throws Exception
     */
    public function deleteSaga(string $type, string $identifier): void
    {
        $this->connection->beginTransaction();
        $this->connection
            ->delete($this->dataTableName, [
                'id' => $identifier,
                'type' => $type,
            ]);
        $this->connection
            ->delete($this->assocTableName, [
                'saga_id' => $identifier,
                'saga_type' => $type,
            ]);
        $this->connection->commit();
    }

    /**
     * @throws Exception
     *
     * @inheritdoc
     */
    public function insertSaga(
        string $type,
        string $identifier,
        object $saga,
        AssociationValues $associationValues,
    ): void {
        $this->connection->beginTransaction();
        $this->connection
            ->insert($this->dataTableName, [
                'id' => $identifier,
                'type' => $type,
                'serialized' => $this->serializer->serialize($saga, 'json'),
                'state' => SagaState::Pending->value,
            ]);

        foreach ($associationValues as $associationValue) {
            $this->connection
                ->insert($this->assocTableName, [
                    'saga_id' => $identifier,
                    'saga_type' => $type,
                    'association_key' => $associationValue->key,
                    'association_value' => $associationValue->value,
                ]);
        }

        $this->connection->commit();
    }

    /**
     * @throws Exception
     */
    public function updateSaga(
        string $type,
        string $identifier,
        object $saga,
        AssociationValues $associationValues,
        SagaState $state,
    ): void {
        $this->connection->beginTransaction();
        $this->connection
            ->update($this->dataTableName, [
                'serialized' => $this->serializer->serialize($saga, 'json'),
                'state' => $state->value,
            ], [
                'id' => $identifier,
                'type' => $type,
            ]);

        foreach ($associationValues->addedAssociations() as $associationValue) {
            $this->connection
                ->insert($this->assocTableName, [
                    'saga_id' => $identifier,
                    'saga_type' => $type,
                    'association_key' => $associationValue->key,
                    'association_value' => $associationValue->value,
                ]);
        }

        foreach ($associationValues->removedAssociations() as $associationValue) {
            $this->connection
                ->delete($this->assocTableName, [
                    'saga_id' => $identifier,
                    'saga_type' => $type,
                    'association_key' => $associationValue->key,
                    'association_value' => $associationValue->value,
                ]);
        }

        $this->connection->commit();
    }

    /**
     * @throws Exception
     */
    protected function readAssociationValues(string $identifier): AssociationValues
    {
        $assocList = $this->connection
            ->prepare(sprintf(
                'SELECT association_key, association_value FROM %s WHERE saga_id = ?',
                $this->assocTableName,
            ))
            ->executeQuery([$identifier])
            ->fetchAllAssociative();

        return new AssociationValues(\array_map(
            static fn (array $assoc) => new AssociationValue($assoc['association_key'], $assoc['association_value']),
            $assocList,
        ));
    }

    /**
     * @throws SchemaException
     * @throws Exception
     */
    public function setup(): void
    {
        $schemaManager = $this->connection->createSchemaManager();
        $schema = new Schema([], [], $schemaManager->createSchemaConfig());
        $this->addDataTableSchema($schema);
        $this->addAssocTableSchema($schema);
        $schemaDiff = $schemaManager->createComparator()->compareSchemas($schemaManager->createSchema(), $schema);
        foreach ($schemaDiff->toSaveSql($this->connection->getDatabasePlatform()) as $sql) {
            $this->connection->executeStatement($sql);
        }
    }

    /**
     * @throws SchemaException
     */
    protected function addDataTableSchema(Schema $schema): void
    {
        $table = $schema->createTable($this->dataTableName);
        $table->addColumn('id', Types::STRING)
            ->setNotnull(true);
        $table->addColumn('type', Types::STRING)
            ->setNotnull(true);
        $table->addColumn('serialized', Types::TEXT)
            ->setNotnull(true);
        $table->addColumn('state', Types::SMALLINT)
            ->setNotnull(true);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['type']);
    }

    /**
     * @throws SchemaException
     */
    protected function addAssocTableSchema(Schema $schema): void
    {
        $table = $schema->createTable($this->assocTableName);
        $table->addColumn('saga_id', Types::STRING)
            ->setNotnull(true);
        $table->addColumn('saga_type', Types::STRING)
            ->setNotnull(true);
        $table->addColumn('association_key', Types::STRING)
            ->setNotnull(true);
        $table->addColumn('association_value', Types::STRING)
            ->setNotnull(true);
        $table->setPrimaryKey(['saga_id', 'association_key']);
        $table->addIndex(['saga_type', 'association_key', 'association_value']);
        $table->addForeignKeyConstraint(
            $this->dataTableName,
            ['saga_id', 'saga_type'],
            ['id', 'type'],
            ['onDelete' => 'CASCADE'],
        );
    }
}
