<?php declare(strict_types=1);

namespace Brzuchal\Saga\Association;

use Stringable;

final class AssociationValue
{
    protected string $value;

    public function __construct(
        protected string $key,
        string|Stringable $value,
    ) {
        $this->value = (string) $value;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        if ($this === $other) {
            return true;
        }

        return $this->key === $other->key
            && $this->value === $other->value;
    }
}
