<?php

declare(strict_types=1);

namespace Brzuchal\Saga\Tests\Repository;

use Brzuchal\Saga\Association\AssociationResolver;
use Brzuchal\Saga\Association\AssociationValue;
use Brzuchal\Saga\Association\AssociationValues;
use Brzuchal\Saga\Association\PropertyNameEvaluator;
use Brzuchal\Saga\Mapping\SagaMetadata;
use Brzuchal\Saga\Mapping\SagaMethodMetadata;
use Brzuchal\Saga\Repository\SimpleSagaRepository;
use Brzuchal\Saga\SagaInstance;
use Brzuchal\Saga\SagaState;
use Brzuchal\Saga\Store\SagaStore;
use Brzuchal\Saga\Store\SimpleSagaStoreEntry;
use Brzuchal\Saga\Tests\Fixtures\FooMessage;
use Brzuchal\Saga\Tests\Fixtures\FooSaga;
use PHPUnit\Framework\TestCase;

class SimpleSagaRepositoryTest extends TestCase
{
    protected SagaMetadata $metadata;

    protected function setUp(): void
    {
        $associationResolver = new AssociationResolver('id', new PropertyNameEvaluator('id'));
        $this->metadata = new SagaMetadata(FooSaga::class, static fn () => new FooSaga(), [
            new SagaMethodMetadata('foo', [FooMessage::class], $associationResolver),
        ]);
    }

    public function testCreate(): void
    {
        $store = $this->createMock(SagaStore::class);
        $repository = new SimpleSagaRepository($store, $this->metadata);
        $this->assertEquals(FooSaga::class, $repository->getType());
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
                $this->equalTo(FooSaga::class),
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
            ->with($this->equalTo(FooSaga::class), $this->equalTo($identifier))
            ->willReturn(new SimpleSagaStoreEntry(new FooSaga(), new AssociationValues([])));
        $repository = new SimpleSagaRepository($store, $this->metadata);
        $instance = $repository->loadSaga($identifier);
        $this->assertEquals(FooSaga::class, $instance->getType());
        $this->assertInstanceOf(FooSaga::class, $instance->instance);
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
                $this->equalTo(FooSaga::class),
                $this->anything(),
                $this->isInstanceOf(FooSaga::class),
                $this->isInstanceOf(AssociationValues::class),
            );
        $repository = new SimpleSagaRepository($store, $this->metadata);
        $instance = $repository->createNewSaga(new FooMessage($identifier), new AssociationValue('id', $identifier));
        $this->assertEquals(FooSaga::class, $instance->getType());
        $this->assertInstanceOf(FooSaga::class, $instance->instance);
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
            ->with($this->equalTo(FooSaga::class), $this->equalTo($identifier));
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
                $this->equalTo(FooSaga::class),
                $this->anything(),
                $this->isInstanceOf(FooSaga::class),
                $this->isInstanceOf(AssociationValues::class),
            );
        $repository = new SimpleSagaRepository($store, $this->metadata);
        $repository->storeSaga(new SagaInstance(
            $identifier,
            new FooSaga(),
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
