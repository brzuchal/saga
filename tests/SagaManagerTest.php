<?php

namespace Brzuchal\Saga\Tests;

use Brzuchal\Saga\Association\AssociationResolver;
use Brzuchal\Saga\Association\AssociationValue;
use Brzuchal\Saga\Association\PropertyNameEvaluator;
use Brzuchal\Saga\Mapping\SagaMetadata;
use Brzuchal\Saga\Mapping\SagaMetadataRepository;
use Brzuchal\Saga\Mapping\SagaMethodMetadata;
use Brzuchal\Saga\SagaManager;
use Brzuchal\Saga\Store\InMemorySagaStore;
use Brzuchal\Saga\Tests\Fixtures\Foo;
use Brzuchal\Saga\Tests\Fixtures\FooMessage;
use PHPUnit\Framework\TestCase;

class SagaManagerTest extends TestCase
{
    private InMemorySagaStore $store;
    private SagaMetadataRepository $repository;

    protected function setUp(): void
    {
        $this->store = new InMemorySagaStore();
        $this->repository = new SagaMetadataRepository();
        $metadata = new SagaMetadata(
            Foo::class,
            fn () => new Foo(),
            [new SagaMethodMetadata(
                'foo',
                [FooMessage::class],
                new AssociationResolver('id', new PropertyNameEvaluator('id'))
            )],
        );
        $this->repository->add($metadata);
    }

    public function testInstanceCreation(): void
    {
        $manager = new SagaManager($this->store, $this->repository);
        $manager(new FooMessage(456));
        $identifiers = $this->store->findSagas(
            Foo::class,
            new AssociationValue('id', 456),
        );
        $this->assertNotEmpty($identifiers);
        $instance = $this->store->loadSaga(Foo::class, $identifiers[0]);
        $this->assertInstanceOf(Foo::class, $instance);
        $this->assertTrue($instance->fooInvoked);
    }
}
