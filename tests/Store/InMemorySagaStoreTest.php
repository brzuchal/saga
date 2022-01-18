<?php declare(strict_types=1);

namespace Brzuchal\Saga\Tests\Store;

use Brzuchal\Saga\Association\AssociationValue;
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
        $found = $store->findSagas(Foo::class, new AssociationValue('id', 123));
        $this->assertEmpty($found);
    }

    public function testInsertWhenEmpty(): void
    {
        $store = new InMemorySagaStore();
        $associationValue = new AssociationValue('id', 123);
        $store->insertSaga(Foo::class, self::ID, new Foo(), [$associationValue]);
        $found = $store->findSagas(Foo::class, $associationValue);
        $this->assertNotEmpty($found);
        $this->assertContainsOnly('string', $found);
        $this->assertEquals(self::ID, $found[0]);
    }

    public function testInsertAndLoad(): void
    {
        $store = new InMemorySagaStore();
        $associationValue = new AssociationValue('id', 123);
        $store->insertSaga(Foo::class, self::ID, new Foo(), [$associationValue]);
        $entry = $store->loadSaga(Foo::class, self::ID);
        $this->assertEquals(SagaState::Pending, $entry->state());
        $this->assertInstanceOf(Foo::class, $entry->object());
        $this->assertEquals([$associationValue], $entry->associationValues());
    }

    public function testUpdateAndLoad(): void
    {
        $store = new InMemorySagaStore();
        $associationValue = new AssociationValue('id', 123);
        $saga = new Foo();
        $store->insertSaga(Foo::class, self::ID, $saga, [$associationValue]);
        $saga->fooInvoked = true;
        $associationValue2 = new AssociationValue('key', 'bar');
        $store->updateSaga(Foo::class, self::ID, $saga, [$associationValue, $associationValue2], SagaState::Pending);
        $entry = $store->loadSaga(Foo::class, self::ID);
        $this->assertEquals(SagaState::Pending, $entry->state());
        $this->assertInstanceOf(Foo::class, $entry->object());
        $this->assertEquals([$associationValue, $associationValue2], $entry->associationValues());
    }
}
