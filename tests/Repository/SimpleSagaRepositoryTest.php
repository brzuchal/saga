<?php

namespace Brzuchal\Saga\Tests\Repository;

use Brzuchal\Saga\Association\AssociationResolver;
use Brzuchal\Saga\Association\AssociationValue;
use Brzuchal\Saga\Association\AssociationValues;
use Brzuchal\Saga\Association\PropertyNameEvaluator;
use Brzuchal\Saga\Mapping\SagaMetadata;
use Brzuchal\Saga\Mapping\SagaMethodMetadata;
use Brzuchal\Saga\Repository\SimpleSagaRepository;
use Brzuchal\Saga\SagaCreationPolicy;
use Brzuchal\Saga\SagaInstance;
use Brzuchal\Saga\SagaState;
use Brzuchal\Saga\Store\SagaStore;
use Brzuchal\Saga\Store\SimpleSagaStoreEntry;
use Brzuchal\Saga\Tests\Fixtures\Foo;
use Brzuchal\Saga\Tests\Fixtures\FooMessage;
use PHPUnit\Framework\TestCase;

class SimpleSagaRepositoryTest extends TestCase
{
    protected SagaMetadata $metadata;

    protected function setUp(): void
    {
        $associationResolver = new AssociationResolver('id', new PropertyNameEvaluator('id'));
        $this->metadata = new SagaMetadata(Foo::class, fn () => new Foo(), [
            new SagaMethodMetadata('foo', [FooMessage::class], $associationResolver),
        ]);
    }

    public function testCreate(): void
    {
        $store = $this->createMock(SagaStore::class);
        $repository = new SimpleSagaRepository($store, $this->metadata);
        $this->assertEquals(Foo::class, $repository->getType());
    }

    public function testSupports(): void
    {
        $store = $this->createMock(SagaStore::class);
        $repository = new SimpleSagaRepository($store, $this->metadata);
        $this->assertTrue($repository->supports(new FooMessage()));
    }

    public function testFindSagas(): void
    {
        $store = $this->createMock(SagaStore::class);
        $identifier = '4218503c-2724-4f01-8b74-8c5797e5c73f';
        $message = new FooMessage();
        $store->expects($this->once())
            ->method('findSagas')
            ->with(
                $this->equalTo(Foo::class),
                $this->isInstanceOf(AssociationValue::class),
            )
            ->willReturn([$identifier]);
        $repository = new SimpleSagaRepository($store, $this->metadata);
        $this->assertEquals([$identifier], $repository->findSagas($message));
    }

    public function testLoadSaga(): void
    {
        $store = $this->createMock(SagaStore::class);
        $identifier = '4218503c-2724-4f01-8b74-8c5797e5c73f';
        $store->expects($this->once())
            ->method('loadSaga')
            ->with($this->equalTo(Foo::class), $this->equalTo($identifier))
            ->willReturn(new SimpleSagaStoreEntry(new Foo(), new AssociationValues([])));
        $repository = new SimpleSagaRepository($store, $this->metadata);
        $instance = $repository->loadSaga($identifier);
        $this->assertEquals(Foo::class, $instance->getType());
        $this->assertInstanceOf(Foo::class, $instance->instance);
        $this->assertEquals($identifier, $instance->id);
        $this->assertCount(0, $instance->associationValues);
        $this->assertSame(SagaState::Pending, $instance->getState());
    }

    public function testCreateNewSaga(): void
    {
        $store = $this->createMock(SagaStore::class);
        $identifier = '4218503c-2724-4f01-8b74-8c5797e5c73f';
        $store->expects($this->once())
            ->method('insertSaga')
            ->with(
                $this->equalTo(Foo::class),
                $this->anything(),
                $this->isInstanceOf(Foo::class),
                $this->isInstanceOf(AssociationValues::class),
            );
        $repository = new SimpleSagaRepository($store, $this->metadata);
        $instance = $repository->createNewSaga(new FooMessage($identifier), new AssociationValue('id', $identifier));
        $this->assertEquals(Foo::class, $instance->getType());
        $this->assertInstanceOf(Foo::class, $instance->instance);
        $this->assertNotEmpty($instance->id);
        $this->assertCount(1, $instance->associationValues);
        $this->assertSame(SagaState::Pending, $instance->getState());
    }

    public function testDeleteSaga(): void
    {
        $store = $this->createMock(SagaStore::class);
        $identifier = '4218503c-2724-4f01-8b74-8c5797e5c73f';
        $store->expects($this->once())
            ->method('deleteSaga')
            ->with($this->equalTo(Foo::class), $this->equalTo($identifier));
        $repository = new SimpleSagaRepository($store, $this->metadata);
        $repository->deleteSaga($identifier);
    }

    public function testStoreSaga(): void
    {
        $store = $this->createMock(SagaStore::class);
        $identifier = '4218503c-2724-4f01-8b74-8c5797e5c73f';
        $store->expects($this->once())
            ->method('updateSaga')
            ->with(
                $this->equalTo(Foo::class),
                $this->anything(),
                $this->isInstanceOf(Foo::class),
                $this->isInstanceOf(AssociationValues::class),
            );
        $repository = new SimpleSagaRepository($store, $this->metadata);
        $repository->storeSaga(new SagaInstance(
            $identifier,
            new Foo(),
            new AssociationValues([]),
            $this->metadata,
        ));
    }

    public function testInitializationPolicy(): void
    {
        $store = $this->createMock(SagaStore::class);
        $repository = new SimpleSagaRepository($store, $this->metadata);
        $id = 'dd906272-e323-4ab5-8904-95b8a53d19e7';
        $policy = $repository->initializationPolicy(new FooMessage($id));
        $this->assertFalse($policy->createAlways());
        $this->assertFalse($policy->createIfNoneFound());
        $this->assertEquals('id', $policy->initialAssociationValue()->key);
        $this->assertEquals($id, $policy->initialAssociationValue()->value);
    }
}
