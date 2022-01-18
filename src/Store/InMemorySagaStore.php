<?php declare(strict_types=1);

namespace Brzuchal\Saga\Store;

use Brzuchal\Saga\Association\AssociationValue;
use Brzuchal\Saga\Store\SimpleSagaStoreEntry;
use Brzuchal\Saga\Repository\SagaStore;
use Brzuchal\Saga\Repository\SagaStoreEntry;
use Brzuchal\Saga\SagaInstanceNotFound;
use Brzuchal\Saga\SagaState;

final class InMemorySagaStore implements SagaStore
{
    /** @psalm-var array<class-string, array<string, SimpleSagaStoreEntry>> */
    protected array $instances = [];

    /** @inheritdoc */
    public function findSagas(string $type, AssociationValue $associationValue): array
    {
        if (!\array_key_exists($type, $this->instances)) {
            return [];
        }

        $found = [];
        foreach ($this->instances[$type] as $identifier => $instance) {
            foreach ($instance->associationValues() as $existingAssociationValue) {
                if (!$existingAssociationValue->equals($associationValue)) {
                    continue;
                }

                $found[] = $identifier;
            }
        }

        return $found;
    }

    /** @inheritdoc */
    public function loadSaga(string $type, string $identifier): SagaStoreEntry
    {
        if (!\array_key_exists($type, $this->instances)) {
            throw SagaInstanceNotFound::unableToLoad($type, $identifier);
        }

        if (!\array_key_exists($identifier, $this->instances[$type])) {
            throw SagaInstanceNotFound::unableToLoad($type, $identifier);
        }

        return $this->instances[$type][$identifier];
    }

    /** @inheritdoc */
    public function deleteSaga(string $type, string $identifier): void
    {
        unset($this->instances[$type][$identifier]);
    }

    /** @inheritdoc */
    public function insertSaga(string $type, string $identifier, object $saga, array $associationValues): void
    {
        $this->instances[$type][$identifier] = new SimpleSagaStoreEntry($saga, $associationValues);
    }

    /** @inheritdoc */
    public function updateSaga(string $type, string $identifier, object $saga, array $associationValues, SagaState $state): void
    {
        $this->instances[$type][$identifier] = new SimpleSagaStoreEntry($saga, $associationValues, $state);
    }
}