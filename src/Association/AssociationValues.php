<?php

declare(strict_types=1);

namespace Brzuchal\Saga\Association;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * @template-implements IteratorAggregate<array-key, AssociationValue>
 */
final class AssociationValues implements IteratorAggregate, Countable
{
    /** @psalm-var list<AssociationValue> */
    protected array $addedValues = [];
    /** @psalm-var list<AssociationValue> */
    protected array $removedValues = [];

    /** @psalm-param list<AssociationValue> $values */
    public function __construct(
        /** @psalm-var list<AssociationValue> */
        protected array $values,
    ) {
    }

    public function contains(AssociationValue $associationValue): bool
    {
        return $this->arrayContains($this->values, $associationValue);
    }

    public function add(AssociationValue $associationValue): bool
    {
        if ($this->contains($associationValue)) {
            return false;
        }

        $this->values[] = $associationValue;
        if ($this->arrayContains($this->removedValues, $associationValue)) {
            $this->removedValues = \array_values(\array_filter(
                $this->removedValues,
                static fn (AssociationValue $existing) => ! $existing->equals($associationValue),
            ));
        } else {
            $this->addedValues[] = $associationValue;
        }

        return true;
    }

    public function remove(AssociationValue $associationValue): bool
    {
        if (! $this->contains($associationValue)) {
            return false;
        }

        $this->values = \array_values(\array_filter(
            $this->values,
            static fn (AssociationValue $existing) => ! $existing->equals($associationValue),
        ));

        if ($this->arrayContains($this->addedValues, $associationValue)) {
            $this->addedValues = \array_values(\array_filter(
                $this->addedValues,
                static fn (AssociationValue $existing) => ! $existing->equals($associationValue),
            ));
        } else {
            $this->removedValues[] = $associationValue;
        }

        return true;
    }

    /** @psalm-return list<AssociationValue> */
    public function removedAssociations(): array
    {
        return $this->removedValues;
    }

    /** @psalm-return list<AssociationValue> */
    public function addedAssociations(): array
    {
        return $this->addedValues;
    }

    /**
     * @psalm-return Traversable<array-key, AssociationValue>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->values);
    }

    /**
     * @psalm-param list<AssociationValue> $values
     */
    protected function arrayContains(array $values, AssociationValue $associationValue): bool
    {
        foreach ($values as $existingAssociationValue) {
            if (! $existingAssociationValue->equals($associationValue)) {
                continue;
            }

            return true;
        }

        return false;
    }

    public function count(): int
    {
        return \count($this->values);
    }
}
