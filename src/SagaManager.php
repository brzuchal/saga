<?php

declare(strict_types=1);

namespace Brzuchal\Saga;

use Brzuchal\Saga\Association\AssociationValue;
use Brzuchal\Saga\Mapping\IncompleteSagaMetadata;
use Brzuchal\Saga\Mapping\SagaMetadata;

/**
 * Basic implementation of {@see SagaInstance} management for single {@see SagaMetadata} type.
 * Provides support for Saga lifecycle management and handling of events.
 */
final class SagaManager
{
    public function __construct(
        protected SagaRepository $repository,
    ) {
    }

    /**
     * @throws SagaInstanceNotFound
     * @throws IncompleteSagaMetadata
     * @throws SagaRejected
     */
    public function __invoke(object $message): void
    {
        if (! $this->repository->supports($message)) {
            return;
        }

        $nonInvokedSaga = true;
        foreach ($this->repository->findSagas($message) as $identifier) {
            $this->doInvokeSaga($this->repository->loadSaga($identifier), $message);
            $nonInvokedSaga = false;
        }

        $initializationPolicy = $this->repository->initializationPolicy($message);
        if ($this->shouldCreateNewSaga($nonInvokedSaga, $initializationPolicy)) {
            $this->startNewSaga($message, $initializationPolicy->initialAssociationValue());
        } elseif ($nonInvokedSaga) {
            throw SagaInstanceNotFound::unableToFind(
                $this->repository->getType(),
                $initializationPolicy->initialAssociationValue(),
            );
        }
    }

    /**
     * @throws IncompleteSagaMetadata
     * @throws SagaRejected
     */
    protected function doInvokeSaga(SagaInstance $instance, object $message): void
    {
        if (! $instance->canHandle($message)) {
            return;
        }

        $instance->handle($message);
        $this->repository->storeSaga($instance);
    }

    /**
     * @throws IncompleteSagaMetadata
     * @throws SagaRejected
     */
    protected function startNewSaga(object $message, AssociationValue $associationValue): void
    {
        $instance = $this->repository->createNewSaga($message, $associationValue);
        if ($instance === null) {
            return;
        }

        $this->doInvokeSaga($instance, $message);
    }

    protected function shouldCreateNewSaga(bool $nonInvokedSaga, SagaInitializationPolicy $initializationPolicy): bool
    {
        if ($initializationPolicy->createAlways()) {
            return true;
        }

        return $initializationPolicy->createIfNoneFound() && $nonInvokedSaga === true;
    }
}
