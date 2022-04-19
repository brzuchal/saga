<?php

declare(strict_types=1);

namespace Brzuchal\Saga\Repository;

use Brzuchal\Saga\Association\AssociationValue;
use Brzuchal\Saga\Association\AssociationValues;
use Brzuchal\Saga\IdentifierGenerationFailed;
use Brzuchal\Saga\Mapping\IncompleteSagaMetadata;
use Brzuchal\Saga\Mapping\SagaMetadata;
use Brzuchal\Saga\SagaIdentifierGenerator;
use Brzuchal\Saga\SagaInitializationPolicy;
use Brzuchal\Saga\SagaInstance;
use Brzuchal\Saga\SagaRepository;
use Brzuchal\Saga\Store\SagaStore;
use Brzuchal\Saga\Store\SetupableSagaStore;
use Closure;

final class SimpleSagaRepository implements SagaRepository
{
    public function __construct(
        protected SagaStore $store,
        protected Closure $factory,
        protected SagaMetadata $metadata,
        protected SagaIdentifierGenerator $identifierGenerator = new SagaIdentifierGenerator(),
    ) {
    }

    public function getType(): string
    {
        return $this->metadata->type;
    }

    public function supports(object $message): bool
    {
        return $this->metadata->hasHandlerMethod($message);
    }

    /**
     * @throws IncompleteSagaMetadata
     *
     * @inheritdoc
     */
    public function findSagas(object $message): iterable
    {
        if ($this->store instanceof SetupableSagaStore) {
            $this->store->setup();
        }

        return $this->store->findSagas(
            $this->metadata->type,
            $this->metadata->resolveAssociation($message),
        );
    }

    public function loadSaga(string $identifier): SagaInstance
    {
        if ($this->store instanceof SetupableSagaStore) {
            $this->store->setup();
        }

        $entry = $this->store->loadSaga(
            $this->metadata->type,
            $identifier,
            $this->createSaga(),
        );

        return new SagaInstance(
            $identifier,
            $entry->object(),
            $entry->associationValues(),
            $this->metadata,
            $entry->state(),
        );
    }

    public function createNewSaga(object $message, AssociationValue $associationValue): SagaInstance|null
    {
        $instance = new SagaInstance(
            $this->identifierGenerator->generateIdentifier(),
            $this->createSaga(),
            new AssociationValues([$associationValue]),
            $this->metadata,
        );
        if ($this->store instanceof SetupableSagaStore) {
            $this->store->setup();
        }

        $this->store->insertSaga(
            $instance->getType(),
            $instance->id,
            $instance->instance,
            $instance->associationValues,
        );

        return $instance;
    }

    public function deleteSaga(string $identifier): void
    {
        if ($this->store instanceof SetupableSagaStore) {
            $this->store->setup();
        }

        $this->store->deleteSaga($this->metadata->type, $identifier);
    }

    public function storeSaga(SagaInstance $instance): void
    {
        if ($this->store instanceof SetupableSagaStore) {
            $this->store->setup();
        }

        $this->store->updateSaga(
            $instance->getType(),
            $instance->id,
            $instance->instance,
            $instance->associationValues,
            $instance->getState(),
        );
    }

    /**
     * @throws IncompleteSagaMetadata
     */
    public function initializationPolicy(object $message): SagaInitializationPolicy
    {
        return new SagaInitializationPolicy(
            $this->metadata->creationPolicy($message),
            $this->metadata->resolveAssociation($message),
        );
    }

    protected function createSaga(): object
    {
        return ($this->factory)();
    }
}
