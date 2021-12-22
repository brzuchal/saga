<?php declare(strict_types=1);

namespace Brzuchal\Saga\Store;

use Brzuchal\Saga\Association\AssociationValue;

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
     */
    public function loadSaga(string $type, string $identifier): object|null;

    /**
     * @psalm-param class-string $type
     */
    public function deleteSaga(string $type, string $identifier): void;

    /**
     * @psalm-param class-string $type
     * @psalm-param iterable<AssociationValue> $associationValues
     */
    public function insertSaga(string $type, string $identifier, object $saga, iterable $associationValues): void;

    /**
     * @psalm-param class-string $type
     * @psalm-param iterable<AssociationValue> $associationValues
     */
    public function updateSaga(string $type, string $identifier, object $saga, iterable $associationValues): void;
}
