<?php

namespace Brzuchal\Saga\Tests\Repository;

use Brzuchal\Saga\Association\AssociationResolver;
use Brzuchal\Saga\Association\AssociationValue;
use Brzuchal\Saga\Association\PropertyNameEvaluator;
use Brzuchal\Saga\Mapping\SagaMetadata;
use Brzuchal\Saga\Mapping\SagaMethodMetadata;
use Brzuchal\Saga\Repository\SimpleSagaRepository;
use Brzuchal\Saga\Store\SagaStore;
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
        $associationResolver = new AssociationResolver('id', new PropertyNameEvaluator('id'));
        $metadata = new SagaMetadata(Foo::class, fn () => new Foo(), [
            new SagaMethodMetadata('foo', [FooMessage::class], $associationResolver),
        ]);
        $identifier = '4218503c-2724-4f01-8b74-8c5797e5c73f';
        $message = new FooMessage();
        $store->expects($this->once())
            ->method('findSagas')
            ->with($this->equalTo(Foo::class), $this->isInstanceOf(AssociationValue::class))
            ->willReturn([$identifier]);
        $repository = new SimpleSagaRepository($store, $metadata);
        $this->assertEquals([$identifier], $repository->findSagas($message));
    }
}
