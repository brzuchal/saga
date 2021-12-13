<?php declare(strict_types=1);

namespace Brzuchal\Saga\Association;

final class AssociationValue
{
    public function __construct(
        protected string $key,
        protected $value,
    ) {
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->key === $other->key && $this->value === $other->value;
    }
}
