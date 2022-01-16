<?php declare(strict_types=1);

namespace Brzuchal\Saga;

interface SagaRepository
{
    /**
     * @return list<string>
     */
    public function findSagas(object $message, bool|null $active = null): iterable;

    /**
     * @throws SagaInstanceNotFound if saga instance cannot be loaded from the store
     */
    public function loadSaga(string $identifier): SagaInstance;

    public function createNewSaga(object $message): SagaInstance|null;

    public function deleteSaga(string $identifier): void;

    public function storeSaga(SagaInstance $instance): void;
}
