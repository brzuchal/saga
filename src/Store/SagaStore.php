<?php declare(strict_types=1);

namespace Brzuchal\Saga\Store;

use Brzuchal\Saga\Association\AssociationValue;
use Brzuchal\Saga\Association\AssociationValues;
use Brzuchal\Saga\SagaInstanceNotFound;
use Brzuchal\Saga\SagaState;

/**
 * Provides a mechanism to find, load update or delete sagas from the underlying storage.
 */
interface SagaStore
{
    /**
     * @psalm-param class-string $type
     * @return list<string>
     */
    public function findSagas(string $type, AssociationValue $associationValue): iterable;

    /**
     * @psalm-param class-string $type
     * @throws SagaInstanceNotFound if saga instance cannot be loaded from the store
     */
    public function loadSaga(string $type, string $identifier): SagaStoreEntry;

    /**
     * @psalm-param class-string $type
     */
    public function deleteSaga(string $type, string $identifier): void;

    /**
     * @psalm-param class-string $type
     */
    public function insertSaga(string $type, string $identifier, object $saga, AssociationValues $associationValues): void;

    /**
     * @psalm-param class-string $type
     */
    public function updateSaga(string $type, string $identifier, object $saga, AssociationValues $associationValues, SagaState $state): void;
}
