<?php declare(strict_types=1);

namespace Brzuchal\Saga\Repository;

use Brzuchal\Saga\Association\AssociationValue;
use Brzuchal\Saga\SagaInstanceNotFound;

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
     * @psalm-param list<AssociationValue> $associationValues
     */
    public function insertSaga(string $type, string $identifier, object $saga, array $associationValues): void;

    /**
     * @psalm-param class-string $type
     * @psalm-param list<AssociationValue> $associationValues
     */
    public function updateSaga(string $type, string $identifier, object $saga, array $associationValues): void;
}
