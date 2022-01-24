<?php declare(strict_types=1);

namespace Brzuchal\Saga\Tests\Store;

use Brzuchal\Saga\Association\AssociationValue;
use Brzuchal\Saga\Association\AssociationValues;
use Brzuchal\Saga\Store\InMemorySagaStore;
use Brzuchal\Saga\SagaState;
use Brzuchal\Saga\Tests\Fixtures\Foo;
use PHPUnit\Framework\TestCase;

class InMemorySagaStoreTest extends TestCase
{
    protected const ID = 'a127a48f-402d-4e96-9d96-969692b9dbed';

    public function testFindWhenEmpty(): void
    {
        $store = new InMemorySagaStore();
        $found = $store->findSagas(Foo::class, new AssociationValue('id', 'db0b9803-e930-4b67-a44d-121d3d1a8574'));
        $this->assertEmpty($found);
    }

    public function testInsertWhenEmpty(): void
    {
        $store = new InMemorySagaStore();
        $associationValue = new AssociationValue('id', '7334a144-206e-48c7-b6aa-ea8686c615ec');
        $store->insertSaga(Foo::class, self::ID, new Foo(), new AssociationValues([$associationValue]));
        $found = $store->findSagas(Foo::class, $associationValue);
        $this->assertNotEmpty($found);
        $this->assertContainsOnly('string', $found);
        $this->assertEquals(self::ID, $found[0]);
    }

    public function testInsertAndLoad(): void
    {
        $store = new InMemorySagaStore();
        $associationValue = new AssociationValue('id', 'f1194933-f152-4063-9f74-3ee33e5e86bc');
        $store->insertSaga(Foo::class, self::ID, new Foo(), new AssociationValues([$associationValue]));
        $entry = $store->loadSaga(Foo::class, self::ID);
        $this->assertEquals(SagaState::Pending, $entry->state());
        $this->assertInstanceOf(Foo::class, $entry->object());
        $this->assertTrue($entry->associationValues()->contains($associationValue));
    }

    public function testUpdateAndLoad(): void
    {
        $store = new InMemorySagaStore();
        $associationValue = new AssociationValue('id', '2f70e250-c5c8-45c0-86ac-37c8e7201826');
        $saga = new Foo();
        $associationValues = new AssociationValues([$associationValue]);
        $store->insertSaga(Foo::class, self::ID, $saga, $associationValues);
        $saga->fooInvoked = true;
        $associationValue2 = new AssociationValue('key', 'bar');
        $associationValues->add($associationValue2);
        $store->updateSaga(Foo::class, self::ID, $saga, $associationValues, SagaState::Pending);
        $entry = $store->loadSaga(Foo::class, self::ID);
        $this->assertEquals(SagaState::Pending, $entry->state());
        $this->assertInstanceOf(Foo::class, $entry->object());
        $this->assertTrue($entry->associationValues()->contains($associationValue));
        $this->assertTrue($entry->associationValues()->contains($associationValue2));
    }

    public function testDelete(): void
    {
        $store = new InMemorySagaStore();
        $associationValue = new AssociationValue('id', 'e17cf25b-d5f2-4964-8b3d-c4c785e92671');
        $saga = new Foo();
        $store->insertSaga(Foo::class, self::ID, $saga, new AssociationValues([$associationValue]));
        $store->deleteSaga(Foo::class, self::ID);
        $this->assertEmpty($store->findSagas(Foo::class, $associationValue));
    }
}
