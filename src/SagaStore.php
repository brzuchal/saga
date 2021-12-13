<?php declare(strict_types=1);

namespace Brzuchal\Saga;

use Brzuchal\Saga\Association\AssociationValue;

/**
 * Provides a mechanism to find, load update or delete sagas from the underlying storage.
 */
interface SagaStore
{
    /**
     * @param class-string $type
     * @return list<string>
     */
    public function findSagas(string $type, AssociationValue $associationValue): iterable;

    /**
     * @param class-string $type
     */
    public function loadSaga(string $type, string $identifier): ?object;

    /**
     * @param class-string $type
     */
    public function deleteSaga(string $type, string $identifier): void;

    /**
     * @param class-string $type
     * @psalm-param list<AssociationValue>
     */
    public function insertSaga(string $type, string $identifier, object $saga, iterable $associationValues): void;

    /**
     * @param class-string $type
     * @psalm-param list<AssociationValue>
     */
    public function updateSaga(string $type, string $identifier, object $saga, iterable $associationValues): void;
}
