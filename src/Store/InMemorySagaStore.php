<?php declare(strict_types=1);

namespace Brzuchal\Saga\Store;

use Brzuchal\Saga\Association\AssociationValue;

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
            foreach ($instance->associationValues as $existingAssociationValue) {
                if(!$existingAssociationValue->equals($associationValue)) {
                    continue;
                }

                $found[] = $identifier;
            }
        }

        return $found;
    }

    /** @inheritdoc */
    public function loadSaga(string $type, string $identifier): ?object
    {
        if (!\array_key_exists($type, $this->instances)) {
            return null;
        }

        if (!\array_key_exists($identifier, $this->instances[$type])) {
            return null;
        }

        return $this->instances[$type][$identifier]->saga;
    }

    /** @inheritdoc */
    public function deleteSaga(string $type, string $identifier): void
    {
        \assert(\class_exists($type));
        unset($this->instances[$type][$identifier]);
    }

    /** @inheritdoc */
    public function insertSaga(string $type, string $identifier, object $saga, iterable $associationValues): void
    {
        \assert(\class_exists($type));
        $this->instances[$type][$identifier] = new InMemorySagaEntry($saga, $associationValues);
    }

    /** @inheritdoc */
    public function updateSaga(string $type, string $identifier, object $saga, iterable $associationValues): void
    {
        \assert(\class_exists($type));
        $this->instances[$type][$identifier]->saga = $saga;
        $this->instances[$type][$identifier]->associationValues = $associationValues;
    }
}

/** @internal  */
final class InMemorySagaEntry
{
    public function __construct(
        public object $saga,
        /** @psalm-return list<\Brzuchal\Saga\Association\AssociationValue> */
        public iterable $associationValues,
    ) {
    }
}
