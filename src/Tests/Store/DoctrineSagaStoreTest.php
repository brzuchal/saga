<?php

declare(strict_types=1);

namespace Brzuchal\Saga\Tests\Store;

use Brzuchal\Saga\Association\AssociationValue;
use Brzuchal\Saga\Association\AssociationValues;
use Brzuchal\Saga\SagaState;
use Brzuchal\Saga\Store\DoctrineSagaStore;
use Brzuchal\Saga\Tests\Fixtures\FooSaga;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class DoctrineSagaStoreTest extends TestCase
{
    protected Connection $connection;

    protected function setUp(): void
    {
        $this->connection = DriverManager::getConnection(['url' => 'sqlite://:memory:']);
        $this->serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
    }

    public function testSetupStore(): void
    {
        $suffix = \substr(\hash('sha1', (string) \microtime(true)), 0, 8);
        $dataTableName = 'saga_data_' . $suffix;
        $assocTableName = 'saga_assoc_' . $suffix;
        $schemaManager = $this->connection->createSchemaManager();
        $this->assertFalse($schemaManager->tablesExist([$dataTableName, $assocTableName]));
        $store = new DoctrineSagaStore($this->connection, $this->serializer, $assocTableName, $dataTableName);
        $store->setup();
        $this->assertTrue($schemaManager->tablesExist([$dataTableName, $assocTableName]));
        $schemaManager->dropTable($dataTableName);
        $schemaManager->dropTable($assocTableName);
    }

    public function testFindWhenEmpty(): void
    {
        $store = new DoctrineSagaStore($this->connection, $this->serializer);
        $store->setup();
        $found = $store->findSagas(FooSaga::class, new AssociationValue('id', '10d6ff42-48c5-46c1-aa42-76dfbbcb6fe6'));
        $this->assertEmpty($found);
    }

    public function testInsertWhenEmpty(): void
    {
        $store = new DoctrineSagaStore($this->connection, $this->serializer);
        $store->setup();
        $associationValue = new AssociationValue('id', '94155628-2d8e-4a37-bb0c-ad0a1d0eb7dd');
        $identifier = '221154d8-2262-44eb-b49b-19943bbe6924';
        $store->insertSaga(FooSaga::class, $identifier, new FooSaga(), new AssociationValues([$associationValue]));
        $found = $store->findSagas(FooSaga::class, $associationValue);
        $this->assertNotEmpty($found);
        $this->assertContainsOnly('string', $found);
        $this->assertEquals($identifier, $found[0]);
    }

    public function testInsertAndLoad(): void
    {
        $store = new DoctrineSagaStore($this->connection, $this->serializer);
        $store->setup();
        $associationValue = new AssociationValue('id', 'd137e0b5-84b4-4c65-bf5c-cf1c10eebfd4');
        $identifier = '37939e99-53e2-4e82-a657-4c27e5ef1b27';
        $store->insertSaga(FooSaga::class, $identifier, new FooSaga(), new AssociationValues([$associationValue]));
        $entry = $store->loadSaga(FooSaga::class, $identifier);
        $this->assertEquals(SagaState::Pending, $entry->state());
        $this->assertInstanceOf(FooSaga::class, $entry->object());
        $this->assertTrue($entry->associationValues()->contains($associationValue));
    }

    public function testUpdateAndLoad(): void
    {
        $store = new DoctrineSagaStore($this->connection, $this->serializer);
        $store->setup();
        $associationValue = new AssociationValue('id', '336bbb79-6bfa-44e3-a8d0-2c99c3406ca0');
        $saga = new FooSaga();
        $identifier = '2ebc6238-f0b9-43f4-b18c-e885d5d7b17d';
        $associationValues = new AssociationValues([$associationValue]);
        $store->insertSaga(FooSaga::class, $identifier, new FooSaga(), $associationValues);
        $saga->fooInvoked = true;
        $associationValue2 = new AssociationValue('key', 'bar');
        $associationValues->add($associationValue2);
        $store->updateSaga(FooSaga::class, $identifier, $saga, $associationValues, SagaState::Pending);
        $entry = $store->loadSaga(FooSaga::class, $identifier);
        $this->assertEquals(SagaState::Pending, $entry->state());
        $this->assertInstanceOf(FooSaga::class, $entry->object());
        $this->assertTrue($entry->associationValues()->contains($associationValue));
        $this->assertTrue($entry->associationValues()->contains($associationValue2));
    }

    public function testDelete(): void
    {
        $store = new DoctrineSagaStore($this->connection, $this->serializer);
        $store->setup();
        $associationValue = new AssociationValue('id', '09155071-f47f-4aff-8c40-7858ec24dfe1');
        $saga = new FooSaga();
        $identifier = '341c358c-bda4-4893-ac55-ca89f1e60143';
        $associationValues = new AssociationValues([$associationValue]);
        $store->insertSaga(FooSaga::class, $identifier, $saga, $associationValues);
        $store->deleteSaga(FooSaga::class, $identifier);
        $this->assertEmpty($store->findSagas(FooSaga::class, $associationValue));
    }
}
