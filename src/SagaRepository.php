<?php declare(strict_types=1);

namespace Brzuchal\Saga;

use Brzuchal\Saga\Association\AssociationValue;

interface SagaRepository
{
    /**
     * @return class-string
     * TODO: investigate on ways to remove this method
     */
    public function getType(): string;

    public function supports(object $message): bool;

    /**
     * @return list<string>
     */
    public function findSagas(object $message): iterable;

    /**
     * @throws SagaInstanceNotFound if saga instance cannot be loaded from the store
     */
    public function loadSaga(string $identifier): SagaInstance;

    public function createNewSaga(object $message, AssociationValue $associationValue): SagaInstance|null;

    public function deleteSaga(string $identifier): void;

    public function storeSaga(SagaInstance $instance): void;

    public function initializationPolicy(object $message): SagaInitializationPolicy;
}
