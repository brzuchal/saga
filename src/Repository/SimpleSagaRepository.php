<?php declare(strict_types=1);

namespace Brzuchal\Saga\Repository;

use Brzuchal\Saga\IdentifierGenerationFailed;
use Brzuchal\Saga\Mapping\IncompleteSagaMetadata;
use Brzuchal\Saga\Mapping\SagaMetadata;
use Brzuchal\Saga\SagaIdentifierGenerator;
use Brzuchal\Saga\SagaInstance;
use Brzuchal\Saga\SagaRepository;

class SimpleSagaRepository implements SagaRepository
{
    public function __construct(
        protected SagaStore $store,
        protected SagaMetadata $metadata,
        protected SagaIdentifierGenerator $identifierGenerator = new SagaIdentifierGenerator(),
    ) {}

    /**
     * @throws IncompleteSagaMetadata
     */
    public function findSagas(object $message, ?bool $active = null): iterable
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
     * @throws IncompleteSagaMetadata
     * @throws IdentifierGenerationFailed
     */
    public function createNewSaga(object $message): SagaInstance|null
    {
        if (!$this->isCreationPolicySatisfied($message)) {
            return null;
        }

        $associationValue = $this->metadata->resolveAssociation($message);
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
            $instance->associationValues(),
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
            $instance->associationValues(),
        );
    }

    private function isCreationPolicySatisfied(
        /** @psalm-suppress UnusedParam */
        object $message,
    ): bool {
        return true;
    }
}
