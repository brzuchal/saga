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
use Brzuchal\Saga\Tests\Fixtures\BarMessage;
use Brzuchal\Saga\Tests\Fixtures\Foo;
use Brzuchal\Saga\Tests\Fixtures\FooMessage;
use PHPUnit\Framework\TestCase;

class SagaManagerTest extends TestCase
{
    const IDENTIFIER = 'b3f28279-9dd2-4016-88d1-ac1ecab0d661';
    private InMemorySagaStore $store;
    private SagaMetadataRepository $repository;

    protected function setUp(): void
    {
        $this->store = new InMemorySagaStore();
        $this->repository = new SagaMetadataRepository();
        $metadata = new SagaMetadata(
            Foo::class,
            fn () => new Foo(),
            [
                new SagaMethodMetadata(
                    'foo',
                    [FooMessage::class],
                    new AssociationResolver('id', new PropertyNameEvaluator('id'))
                ),
                new SagaMethodMetadata(
                    'bar',
                    [BarMessage::class],
                    new AssociationResolver('str', new PropertyNameEvaluator('bar'))
                ),
            ],
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

    /**
     * @depends testInstanceCreation
     */
    public function testInvoke(): void
    {
        $saga = new Foo();
        $this->store->insertSaga(
            Foo::class,
            self::IDENTIFIER,
            $saga,
            [new AssociationValue('str', 'bar')],
        );
        $manager = new SagaManager($this->store, $this->repository);
        $manager(new BarMessage());
        $identifiers = $this->store->findSagas(
            Foo::class,
            new AssociationValue('str', 'bar'),
        );
        $this->assertTrue($saga->barInvoked);
        $this->assertNotEmpty($identifiers);
        $this->assertEquals(self::IDENTIFIER, $identifiers[0]);
        $instance = $this->store->loadSaga(Foo::class, $identifiers[0]);
        $this->assertInstanceOf(Foo::class, $instance);
        $this->assertEquals($saga, $instance);
    }
}
