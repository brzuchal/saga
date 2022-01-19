<?php declare(strict_types=1);

namespace Brzuchal\Saga;

use Brzuchal\Saga\Association\AssociationValue;
use Brzuchal\Saga\Association\AssociationValues;
use Exception;

final class SagaLifecycle
{
    public function __construct(
        protected SagaState $state,
        public readonly AssociationValues $associationValues,
    ) {
    }

    public function isActive(): bool
    {
        return $this->state === SagaState::Pending;
    }

    public function complete(): void
    {
        $this->state = SagaState::Completed;
    }

    /**
     * @throws SagaRejected
     */
    public function reject(Exception $exception = null): void
    {
        $this->state = SagaState::Rejected;
        throw SagaRejected::create($exception);
    }

    public function associateWith(string $key, mixed $value): self
    {
        $this->associationValues->add(new AssociationValue($key, $value));

        return $this;
    }

    public function removeAssociationWith(string $key, mixed $value): self
    {
        $this->associationValues->remove(new AssociationValue($key, $value));

        return $this;
    }

    public function getState(): SagaState
    {
        return $this->state;
    }
}
