<?php declare(strict_types=1);

namespace Brzuchal\Saga\Mapping;

use Brzuchal\Saga\Association\AssociationResolver;
use Brzuchal\Saga\SagaCreationPolicy;

final class SagaMethodMetadata
{
    public function __construct(
        protected string $name,
        /** @psalm-var list<class-string> */
        protected array $types,
        protected AssociationResolver $associationResolver,
        protected SagaCreationPolicy $creationPolicy = SagaCreationPolicy::NONE,
        protected bool $end = false,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAssociationResolver(): AssociationResolver
    {
        return $this->associationResolver;
    }

    /**
     * @psalm-return list<class-string>
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    public function getCreationPolicy(): SagaCreationPolicy
    {
        return $this->creationPolicy;
    }

    public function getEnd(): bool
    {
        return $this->end;
    }
}
