<?php declare(strict_types=1);

namespace Brzuchal\Saga;

use Brzuchal\Saga\Association\AssociationValues;
use Brzuchal\Saga\Mapping\IncompleteSagaMetadata;
use Brzuchal\Saga\Mapping\SagaMetadata;
use Exception;

final class SagaInstance
{
    public function __construct(
        public readonly string $id,
        public readonly object $instance,
        public readonly AssociationValues $associationValues,
        protected SagaMetadata $metadata,
        protected SagaState $state = SagaState::Pending,
    ) {
    }

    /**
     * @return class-string
     */
    public function getType(): string
    {
        return $this->metadata->type;
    }

    public function lifecycle(): SagaLifecycle
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
            && $this->associationValues->contains($this->metadata->resolveAssociation($message));
    }

    /**
     * @throws IncompleteSagaMetadata
     * @throws SagaRejected
     */
    public function handle(object $message): void
    {
        $method = $this->metadata->findHandlerMethod($message);
        $lifecycle = $this->lifecycle();
        try {
            $this->instance->{$method}($message, $lifecycle);
            if ($this->metadata->isCompleting($message)) {
                $lifecycle->complete();
            }
        } catch (SagaRejected $exception) {
            throw $exception;
        } catch (Exception $exception) {
            $lifecycle->reject($exception);
        } finally {
            $this->state = $lifecycle->getState();
        }
    }
}
