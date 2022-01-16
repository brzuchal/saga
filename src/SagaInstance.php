<?php declare(strict_types=1);

namespace Brzuchal\Saga;

use Brzuchal\Saga\Association\AssociationValue;
use Brzuchal\Saga\Mapping\IncompleteSagaMetadata;
use Brzuchal\Saga\Mapping\SagaMetadata;

final class SagaInstance
{
    /**
     * @psalm-param list<AssociationValue> $associationValues
     */
    public function __construct(
        public readonly string $id,
        public readonly object $instance,
        /** @var list<AssociationValue> */
        protected array $associationValues,
        protected SagaMetadata $metadata,
        protected SagaState $state = SagaState::Pending,
    ) {
    }

    /**
     * @return class-string
     */
    public function getType(): string
    {
        return $this->metadata->getName();
    }

    public function getLifecycle(): SagaLifecycle
    {
        return new SagaLifecycle(
            $this->state,
            $this->associationValues,
        );
    }

    public function getState(): SagaState
    {
        return $this->state;
    }

    /**
     * @throws IncompleteSagaMetadata
     */
    public function canHandle(object $message): bool
    {
        return $this->state === SagaState::Pending
            && $this->containsAssociationValue($this->metadata->resolveAssociation($message));
    }

    /**
     * @throws IncompleteSagaMetadata
     */
    public function handle(object $message): void
    {
        $method = $this->metadata->findHandlerMethod($message);
        $this->instance->{$method}($message, $this->getLifecycle());
        // TODO: apply lifecycle state and merge association values
    }

    /**
     * @psalm-return list<AssociationValue>
     */
    public function associationValues(): array
    {
        return $this->associationValues;
    }

    protected function containsAssociationValue(AssociationValue $associationValue): bool
    {
        foreach ($this->associationValues as $existingAssociationValue) {
            if ($existingAssociationValue->equals($associationValue)) {
                return true;
            }
        }

        return false;
    }
}
