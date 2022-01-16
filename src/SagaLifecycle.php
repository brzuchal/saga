<?php declare(strict_types=1);

namespace Brzuchal\Saga;

use Brzuchal\Saga\Association\AssociationValue;
use Exception;

final class SagaLifecycle
{
    public function __construct(
        protected SagaState $state,
        /** @psalm-var list<AssociationValue> */
        protected array $associationValues,
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

    public function associateValue(string $key, mixed $value): self
    {
        $this->associationValues[] = new AssociationValue($key, $value);

        return $this;
    }

    /**
     * @psalm-return list<AssociationValue>
     */
    public function getAssociationValues(): array
    {
        return $this->associationValues;
    }

    public function getState(): SagaState
    {
        return $this->state;
    }
}
