<?php declare(strict_types=1);

namespace Brzuchal\Saga\Repository;

use Brzuchal\Saga\Association\AssociationValue;
use Brzuchal\Saga\IdentifierGenerationFailed;
use Brzuchal\Saga\Mapping\IncompleteSagaMetadata;
use Brzuchal\Saga\Mapping\SagaMetadata;
use Brzuchal\Saga\SagaIdentifierGenerator;
use Brzuchal\Saga\SagaInitializationPolicy;
use Brzuchal\Saga\SagaInstance;
use Brzuchal\Saga\SagaRepository;

class SimpleSagaRepository implements SagaRepository
{
    public function __construct(
        protected SagaStore $store,
        protected SagaMetadata $metadata,
        protected SagaIdentifierGenerator $identifierGenerator = new SagaIdentifierGenerator(),
    ) {}

    public function getType(): string
    {
        return $this->metadata->getName();
    }

    public function supports(object $message): bool
    {
        return $this->metadata->hasHandlerMethod($message);
    }

    /**
     * @throws IncompleteSagaMetadata
     */
    public function findSagas(object $message): iterable
    {
        return $this->store->findSagas(
            $this->metadata->getName(),
            $this->metadata->resolveAssociation($message),
        );
    }

    public function loadSaga(string $identifier): SagaInstance
    {
        $entry = $this->store->loadSaga(
            $this->metadata->getName(),
            $identifier,
        );

        return new SagaInstance(
            $identifier,
            $entry->object(),
            $entry->associationValues(),
            $this->metadata,
            $entry->state(),
        );
    }

    /**
     * @throws IdentifierGenerationFailed
     */
    public function createNewSaga(object $message, AssociationValue $associationValue): SagaInstance|null
    {
        $instance = new SagaInstance(
            $this->identifierGenerator->generateIdentifier(),
            $this->metadata->newInstance(),
            [$associationValue],
            $this->metadata,
        );
        $this->store->insertSaga(
            $instance->getType(),
            $instance->id,
            $instance->instance,
            $instance->getAssociationValues(),
        );

        return $instance;
    }

    public function deleteSaga(string $identifier): void
    {
        // TODO: Implement deleteSaga() method.
    }

    public function storeSaga(SagaInstance $instance): void
    {
        $this->store->updateSaga(
            $instance->getType(),
            $instance->id,
            $instance->instance,
            $instance->getAssociationValues(),
            $instance->getState(),
        );
    }

    /**
     * @throws IncompleteSagaMetadata
     */
    public function initializationPolicy(object $message): SagaInitializationPolicy
    {
        return new SagaInitializationPolicy(
            $this->metadata->getSagaCreationPolicy($message),
            $this->metadata->resolveAssociation($message),
        );
    }
}
