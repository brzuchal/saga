<?php declare(strict_types=1);

namespace Brzuchal\Saga;

use Brzuchal\Saga\Association\AssociationValue;
use Brzuchal\Saga\Mapping\IncompleteSagaMetadata;
use Brzuchal\Saga\Mapping\SagaMetadata;
use Exception;

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
     * @throws SagaRejected
     */
    public function handle(object $message): void
    {
        $method = $this->metadata->findHandlerMethod($message);
        $lifecycle = $this->getLifecycle();
        try {
            $this->instance->{$method}($message, $lifecycle);
        } catch (SagaRejected $exception) {
            throw $exception;
        } catch (Exception $exception) {
            $lifecycle->reject($exception);
        } finally {
            $this->associationValues = $lifecycle->getAssociationValues();
            $this->state = $lifecycle->getState();
        }
    }

    /**
     * @psalm-return list<AssociationValue>
     */
    public function getAssociationValues(): array
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
