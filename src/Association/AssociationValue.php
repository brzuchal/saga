<?php declare(strict_types=1);

namespace Brzuchal\Saga\Association;

use Stringable;

final class AssociationValue
{
    public readonly string $value;

    public function __construct(
        public readonly string $key,
        string|Stringable $value,
    ) {
        $this->value = (string) $value;
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
