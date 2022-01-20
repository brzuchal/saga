<?php declare(strict_types=1);

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

final class DoctrineSagaStore implements SagaStore, SetupableSagaStore
{
    public const DEFAULT_ASSOC_TABLE_NAME = 'saga_assoc';
    public const DEFAULT_DATA_TABLE_NAME = 'saga_data';

    public function __construct(
        protected Connection $connection,
        protected string $assocTableName = self::DEFAULT_ASSOC_TABLE_NAME,
        protected string $dataTableName = self::DEFAULT_DATA_TABLE_NAME,
    ) {}

    /**
     * @throws Exception
     */
    public function findSagas(string $type, AssociationValue $associationValue): iterable
    {
        return $this->connection
            ->prepare(
            "SELECT saga_id FROM {$this->assocTableName} WHERE association_key = ? AND association_value = ? AND saga_type = ?"
            )
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
    public function loadSaga(string $type, string $identifier): SagaStoreEntry
    {
        $entry = $this->connection
            ->prepare("SELECT serialized, type, state FROM {$this->dataTableName} WHERE id = ?")
            ->executeQuery([$identifier])
            ->fetchAssociative();

        if ($entry === false) {
            throw SagaInstanceNotFound::unableToLoad($type, $identifier);
        }

        return new SimpleSagaStoreEntry(
            \unserialize($entry['serialized']),
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
     * @inheritdoc
     * @throws Exception
     */
    public function insertSaga(string $type, string $identifier, object $saga, AssociationValues $associationValues): void
    {
        $this->connection->beginTransaction();
        $this->connection
            ->insert($this->dataTableName, [
                'id' => $identifier,
                'type' => $type,
                'serialized' => \serialize($saga),
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
    public function updateSaga(string $type, string $identifier, object $saga, AssociationValues $associationValues, SagaState $state): void
    {
        $this->connection->beginTransaction();
        $this->connection
            ->update($this->dataTableName, [
                'serialized' => \serialize($saga),
                'state' => $state->value
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
            ->prepare("SELECT association_key, association_value FROM {$this->assocTableName} WHERE saga_id = ?")
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
