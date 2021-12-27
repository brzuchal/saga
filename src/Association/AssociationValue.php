<?php declare(strict_types=1);

namespace Brzuchal\Saga\Association;

final class AssociationValue
{
    public function __construct(
        protected string $key,
        protected mixed $value,
    ) {
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->key === $other->key &&
            \get_debug_type($this->value) === \get_debug_type($other->value) &&
            $this->value === $other->value;
    }
}
