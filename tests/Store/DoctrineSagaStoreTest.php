<?php

namespace Brzuchal\Saga\Tests\Store;

use Brzuchal\Saga\Association\AssociationValue;
use Brzuchal\Saga\SagaState;
use Brzuchal\Saga\Store\DoctrineSagaStore;
use Brzuchal\Saga\Tests\Fixtures\Foo;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use PHPUnit\Framework\TestCase;

class DoctrineSagaStoreTest extends TestCase
{
    protected Connection $connection;

    protected function setUp(): void
    {
        $this->connection = DriverManager::getConnection([
//            'url' => 'sqlite://var/db.sqlite',
            'url' => 'sqlite://:memory:',
        ]);
    }

    public function testSetupStore(): void
    {
        $suffix = \substr(\hash('sha1', (string) \microtime(true)), 0, 8);
        $dataTableName = 'saga_data_' . $suffix;
        $assocTableName = 'saga_assoc_' . $suffix;
        $schemaManager = $this->connection->createSchemaManager();
        $this->assertFalse($schemaManager->tablesExist([$dataTableName, $assocTableName]));
        $store = new DoctrineSagaStore($this->connection, $assocTableName, $dataTableName);
        $store->setup();
        $this->assertTrue($schemaManager->tablesExist([$dataTableName, $assocTableName]));
        $schemaManager->dropTable($dataTableName);
        $schemaManager->dropTable($assocTableName);
    }

    public function testFindWhenEmpty(): void
    {
        $store = new DoctrineSagaStore($this->connection);
        $store->setup();
        $found = $store->findSagas(Foo::class, new AssociationValue('id', \random_int(1, 999999)));
        $this->assertEmpty($found);
    }

    public function testInsertWhenEmpty(): void
    {
        $store = new DoctrineSagaStore($this->connection);
        $store->setup();
        $associationValue = new AssociationValue('id', \random_int(1, 999999));
        $identifier = '221154d8-2262-44eb-b49b-19943bbe6924';
        $store->insertSaga(Foo::class, $identifier, new Foo(), [$associationValue]);
        $found = $store->findSagas(Foo::class, $associationValue);
        $this->assertNotEmpty($found);
        $this->assertContainsOnly('string', $found);
        $this->assertEquals($identifier, $found[0]);
    }


    public function testInsertAndLoad(): void
    {
        $store = new DoctrineSagaStore($this->connection);
        $store->setup();
        $associationValue = new AssociationValue('id', \random_int(1, 999999));
        $identifier = '37939e99-53e2-4e82-a657-4c27e5ef1b27';
        $store->insertSaga(Foo::class, $identifier, new Foo(), [$associationValue]);
        $entry = $store->loadSaga(Foo::class, $identifier);
        $this->assertEquals(SagaState::Pending, $entry->state());
        $this->assertInstanceOf(Foo::class, $entry->object());
        $this->assertEquals([$associationValue], $entry->associationValues());
    }

    public function testUpdateAndLoad(): void
    {
        $this->markTestIncomplete('Require association to tighten type into string only');
        $store = new DoctrineSagaStore($this->connection);
        $store->setup();
        $associationValue = new AssociationValue('id', \random_int(1, 999999));
        $saga = new Foo();
        $identifier = '2ebc6238-f0b9-43f4-b18c-e885d5d7b17d';
        $store->insertSaga(Foo::class, $identifier, $saga, [$associationValue]);
        $saga->fooInvoked = true;
        $associationValue2 = new AssociationValue('key', 'bar');
        // TODO: tighten association value to string only to avoid comparison issues after value coming from db
        $store->updateSaga(Foo::class, $identifier, $saga, [$associationValue, $associationValue2], SagaState::Pending);
        $entry = $store->loadSaga(Foo::class, $identifier);
        $this->assertEquals(SagaState::Pending, $entry->state());
        $this->assertInstanceOf(Foo::class, $entry->object());
        $this->assertEquals([$associationValue, $associationValue2], $entry->associationValues());
    }
}
