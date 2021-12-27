<?php declare(strict_types=1);

namespace Brzuchal\Saga;

use Brzuchal\Saga\Association\AssociationValue;

class SagaLifecycle
{
    public function __construct(
        protected bool $active,
        /** @psalm-var list<AssociationValue> */
        protected array $associationValues,
    ) {
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function end(): void
    {
        $this->active = false;
    }

    public function associateValue(string $key, mixed $value): self
    {
        $this->associationValues[] = new AssociationValue($key, $value);

        return $this;
    }

    /**
     * @psalm-return list<AssociationValue>
     */
    public function associationValues(): array
    {
        return $this->associationValues;
    }
}
