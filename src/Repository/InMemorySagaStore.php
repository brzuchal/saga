<?php declare(strict_types=1);

namespace Brzuchal\Saga\Repository;

use Brzuchal\Saga\Association\AssociationValue;
use Brzuchal\Saga\SagaInstanceNotFound;
use Brzuchal\Saga\SagaState;

final class InMemorySagaStore implements SagaStore
{
    /** @psalm-var array<class-string, array<string, InMemorySagaEntry>> */
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
        $this->instances[$type][$identifier] = new InMemorySagaEntry($saga, $associationValues);
    }

    /** @inheritdoc */
    public function updateSaga(string $type, string $identifier, object $saga, array $associationValues, SagaState $state): void
    {
        $this->instances[$type][$identifier] = new InMemorySagaEntry($saga, $associationValues, $state);
    }
}

/** @internal  */
final class InMemorySagaEntry implements SagaStoreEntry
{
    public function __construct(
        protected object $saga,
        /** @psalm-var list<AssociationValue> */
        protected array $associationValues,
        protected SagaState $state = SagaState::Pending,
    ) {
    }

    public function object(): object
    {
        return $this->saga;
    }

    /** @inheritdoc */
    public function associationValues(): array
    {
        return $this->associationValues;
    }

    public function state(): SagaState
    {
        return $this->state;
    }
}
