<?php

namespace Brzuchal\Saga\Tests;

use Brzuchal\Saga\Association\AssociationResolver;
use Brzuchal\Saga\Association\AssociationValue;
use Brzuchal\Saga\Association\PropertyNameEvaluator;
use Brzuchal\Saga\Mapping\MappingDriver;
use Brzuchal\Saga\Mapping\SagaMetadata;
use Brzuchal\Saga\Mapping\SagaMetadataFactory;
use Brzuchal\Saga\Mapping\SagaMethodMetadata;
use Brzuchal\Saga\SagaCreationPolicy;
use Brzuchal\Saga\SagaIdentifierGenerator;
use Brzuchal\Saga\SagaInstanceNotFound;
use Brzuchal\Saga\SagaManager;
use Brzuchal\Saga\Repository\InMemorySagaStore;
use Brzuchal\Saga\SagaRejected;
use Brzuchal\Saga\SagaRepository;
use Brzuchal\Saga\SagaRepositoryFactory;
use Brzuchal\Saga\SagaState;
use Brzuchal\Saga\Tests\Fixtures\BarMessage;
use Brzuchal\Saga\Tests\Fixtures\BazMessage;
use Brzuchal\Saga\Tests\Fixtures\Foo;
use Brzuchal\Saga\Tests\Fixtures\FooMessage;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class SagaManagerTest extends TestCase
{
    private const IDENTIFIER = 'b3f28279-9dd2-4016-88d1-ac1ecab0d661';

    private InMemorySagaStore $store;
    private SagaRepository $repository;

    protected function setUp(): void
    {
        $this->store = new InMemorySagaStore();
        $repositoryFactory = new SagaRepositoryFactory(
            $this->store,
            new SagaMetadataFactory([
                new class() implements MappingDriver {
                    public function loadMetadataForClass(string $class): SagaMetadata|null
                    {
                        return new SagaMetadata(
                            Foo::class,
                            fn () => new Foo(),
                            [
                                new SagaMethodMetadata(
                                    'foo',
                                    [FooMessage::class],
                                    new AssociationResolver('id', new PropertyNameEvaluator('id')),
                                    SagaCreationPolicy::IF_NONE_FOUND,
                                ),
                                new SagaMethodMetadata(
                                    'bar',
                                    [BarMessage::class],
                                    new AssociationResolver('str', new PropertyNameEvaluator('bar')),
                                ),
                                new SagaMethodMetadata(
                                    'baz',
                                    [BazMessage::class],
                                    new AssociationResolver('id', new PropertyNameEvaluator('id')),
                                    end: true,
                                ),
                            ],
                        );
                    }
                },
            ]),
            new class(self::IDENTIFIER) extends SagaIdentifierGenerator {
                public function __construct(protected string $identifier)
                {}

                public function generateIdentifier(): string
                {
                    return $this->identifier;
                }
            }
        );
        $this->repository = $repositoryFactory->create(Foo::class);
    }

    public function testInstanceCreation(): void
    {
        $manager = new SagaManager($this->repository);
        $manager(new FooMessage(456));

        $identifiers = $this->store->findSagas(
            Foo::class,
            new AssociationValue('id', 456),
        );
        $this->assertNotEmpty($identifiers);
        $entry = $this->repository->loadSaga($identifiers[0]);
        $this->assertInstanceOf(Foo::class, $entry->instance);
        \assert($entry->instance instanceof Foo);
        $this->assertTrue($entry->instance->fooInvoked);
    }

    /**
     * @depends testInstanceCreation
     */
    public function testInvokeOnInstance(): void
    {
        $manager = new SagaManager($this->repository);
        $manager(new FooMessage(456));
        $manager(new BarMessage());
        $identifiers = $this->store->findSagas(
            Foo::class,
            new AssociationValue('str', 'bar'),
        );

        $this->assertNotEmpty($identifiers);
        $entry = $this->repository->loadSaga($identifiers[0]);
        $this->assertInstanceOf(Foo::class, $entry->instance);
        \assert($entry->instance instanceof Foo);
        $this->assertTrue($entry->instance->barInvoked);
    }

    public function testFailOnMissingInstance(): void
    {
        $manager = new SagaManager($this->repository);
        $this->expectException(SagaInstanceNotFound::class);
        $manager(new BarMessage());
    }

    public function testEndInvokeAndSagaCompletion(): void
    {
        $manager = new SagaManager($this->repository);
        $manager(new FooMessage(456));
        $manager(new BazMessage(456));
        $identifiers = $this->store->findSagas(
            Foo::class,
            new AssociationValue('id', 456),
        );

        $this->assertNotEmpty($identifiers);
        $entry = $this->repository->loadSaga($identifiers[0]);
        $this->assertInstanceOf(Foo::class, $entry->instance);
        \assert($entry->instance instanceof Foo);
        $this->assertTrue($entry->instance->bazInvoked);
        $this->assertEquals(SagaState::Completed, $entry->getState());
    }

    public function testRejectionByMethod(): void
    {
        $manager = new SagaManager($this->repository);
        $manager(new FooMessage(456));
        $this->expectException(SagaRejected::class);
        $manager(new BarMessage(exception: new RuntimeException('Intentional failure')));
        $identifiers = $this->store->findSagas(
            Foo::class,
            new AssociationValue('id', 456),
        );

        $this->assertNotEmpty($identifiers);
        $entry = $this->repository->loadSaga($identifiers[0]);
        $this->assertInstanceOf(Foo::class, $entry->instance);
        \assert($entry->instance instanceof Foo);
        $this->assertTrue($entry->instance->bazInvoked);
        $this->assertEquals(SagaState::Rejected, $entry->getState());
    }

    public function testRejection(): void
    {
        $manager = new SagaManager($this->repository);
        $manager(new FooMessage(456));
        $this->expectException(SagaRejected::class);
        $manager(new BazMessage(456, new RuntimeException('Intentional failure')));
        $identifiers = $this->store->findSagas(
            Foo::class,
            new AssociationValue('id', 456),
        );

        $this->assertNotEmpty($identifiers);
        $entry = $this->repository->loadSaga($identifiers[0]);
        $this->assertInstanceOf(Foo::class, $entry->instance);
        \assert($entry->instance instanceof Foo);
        $this->assertTrue($entry->instance->bazInvoked);
        $this->assertEquals(SagaState::Rejected, $entry->getState());
    }
}
