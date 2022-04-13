<?php

declare(strict_types=1);

namespace Brzuchal\Saga\Tests;

use Brzuchal\Saga\Association\AssociationResolver;
use Brzuchal\Saga\Association\AssociationValue;
use Brzuchal\Saga\Association\PropertyNameEvaluator;
use Brzuchal\Saga\Mapping\MappingDriver;
use Brzuchal\Saga\Mapping\SagaMetadata;
use Brzuchal\Saga\Mapping\SagaMetadataFactory;
use Brzuchal\Saga\Mapping\SagaMethodMetadata;
use Brzuchal\Saga\Repository\SimpleSagaRepositoryFactory;
use Brzuchal\Saga\SagaCreationPolicy;
use Brzuchal\Saga\SagaIdentifierGenerator;
use Brzuchal\Saga\SagaInstanceNotFound;
use Brzuchal\Saga\SagaManager;
use Brzuchal\Saga\SagaRejected;
use Brzuchal\Saga\SagaRepository;
use Brzuchal\Saga\SagaState;
use Brzuchal\Saga\Store\InMemorySagaStore;
use Brzuchal\Saga\Tests\Fixtures\BarMessage;
use Brzuchal\Saga\Tests\Fixtures\BazMessage;
use Brzuchal\Saga\Tests\Fixtures\FooMessage;
use Brzuchal\Saga\Tests\Fixtures\FooSaga;
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
        $repositoryFactory = new SimpleSagaRepositoryFactory(
            $this->store,
            new SagaMetadataFactory([
                new class () implements MappingDriver {
                    public function loadMetadataForClass(string $class): SagaMetadata|null
                    {
                        return new SagaMetadata(
                            FooSaga::class,
                            static fn () => new FooSaga(),
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
            new class (self::IDENTIFIER) extends SagaIdentifierGenerator {
                public function __construct(protected string $identifier)
                {
                }

                public function generateIdentifier(): string
                {
                    return $this->identifier;
                }
            }
        );
        $this->repository = $repositoryFactory->create(FooSaga::class);
    }

    public function testInstanceCreation(): void
    {
        $manager = new SagaManager($this->repository);
        $id = 'c09a27cc-c17a-46eb-bc15-e59bab41b766';
        $manager(new FooMessage($id));

        $identifiers = $this->store->findSagas(
            FooSaga::class,
            new AssociationValue('id', $id),
        );
        $this->assertNotEmpty($identifiers);
        $entry = $this->repository->loadSaga($identifiers[0]);
        $this->assertInstanceOf(FooSaga::class, $entry->instance);
        \assert($entry->instance instanceof FooSaga);
        $this->assertTrue($entry->instance->fooInvoked);
    }

    /**
     * @depends testInstanceCreation
     */
    public function testInvokeOnInstance(): void
    {
        $manager = new SagaManager($this->repository);
        $manager(new FooMessage('6816aea8-404a-4598-b748-cfcaedbb886b'));
        $manager(new BarMessage());
        $identifiers = $this->store->findSagas(
            FooSaga::class,
            new AssociationValue('str', 'bar'),
        );

        $this->assertNotEmpty($identifiers);
        $entry = $this->repository->loadSaga($identifiers[0]);
        $this->assertInstanceOf(FooSaga::class, $entry->instance);
        \assert($entry->instance instanceof FooSaga);
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
        $id = '97e7633c-e66c-4d48-b8fe-cd9cde1d3fa6';
        $manager(new FooMessage($id));
        $manager(new BazMessage($id));
        $identifiers = $this->store->findSagas(
            FooSaga::class,
            new AssociationValue('id', $id),
        );

        $this->assertNotEmpty($identifiers);
        $entry = $this->repository->loadSaga($identifiers[0]);
        $this->assertInstanceOf(FooSaga::class, $entry->instance);
        \assert($entry->instance instanceof FooSaga);
        $this->assertTrue($entry->instance->bazInvoked);
        $this->assertEquals(SagaState::Completed, $entry->getState());
    }

    public function testRejectionByMethod(): void
    {
        $manager = new SagaManager($this->repository);
        $id = '6d46ab6d-5433-4692-8344-df00ba832ba0';
        $manager(new FooMessage($id));
        $this->expectException(SagaRejected::class);
        $manager(new BarMessage(exception: new RuntimeException('Intentional failure')));
        $identifiers = $this->store->findSagas(
            FooSaga::class,
            new AssociationValue('id', $id),
        );

        $this->assertNotEmpty($identifiers);
        $entry = $this->repository->loadSaga($identifiers[0]);
        $this->assertInstanceOf(FooSaga::class, $entry->instance);
        \assert($entry->instance instanceof FooSaga);
        $this->assertTrue($entry->instance->bazInvoked);
        $this->assertEquals(SagaState::Rejected, $entry->getState());
    }

    public function testRejection(): void
    {
        $manager = new SagaManager($this->repository);
        $id = 'b4d353d9-a30a-4a63-861f-034b078f0904';
        $manager(new FooMessage($id));
        $this->expectException(SagaRejected::class);
        $manager(new BazMessage($id, new RuntimeException('Intentional failure')));
        $identifiers = $this->store->findSagas(
            FooSaga::class,
            new AssociationValue('id', $id),
        );

        $this->assertNotEmpty($identifiers);
        $entry = $this->repository->loadSaga($identifiers[0]);
        $this->assertInstanceOf(FooSaga::class, $entry->instance);
        \assert($entry->instance instanceof FooSaga);
        $this->assertTrue($entry->instance->bazInvoked);
        $this->assertEquals(SagaState::Rejected, $entry->getState());
    }
}
