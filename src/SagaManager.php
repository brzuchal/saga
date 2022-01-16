<?php declare(strict_types=1);

namespace Brzuchal\Saga;

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
     */
    public function __invoke(object $message): void
    {
        $nonInvokedSaga = true;
        foreach ($this->repository->findSagas($message, active: true) as $identifier) {
            $this->doInvokeSaga($this->repository->loadSaga($identifier), $message);
            $nonInvokedSaga = false;
        }
        if ($nonInvokedSaga === true) {
            $this->startNewSaga($message);
        }
    }

    /**
     * @throws IncompleteSagaMetadata
     */
    protected function doInvokeSaga(SagaInstance $instance, object $message): void
    {
        if (!$instance->canHandle($message)) {
            return;
        }

        $instance->handle($message);
        $this->repository->storeSaga($instance);
    }

    /**
     * @throws IncompleteSagaMetadata
     */
    protected function startNewSaga(object $message): void
    {
        $instance = $this->repository->createNewSaga($message);
        if ($instance === null) {
            return;
        }

        $this->doInvokeSaga($instance, $message);
    }
}
