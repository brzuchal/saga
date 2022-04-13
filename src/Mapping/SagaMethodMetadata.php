<?php

declare(strict_types=1);

namespace Brzuchal\Saga\Mapping;

use Brzuchal\Saga\Association\AssociationResolver;
use Brzuchal\Saga\SagaCreationPolicy;

final class SagaMethodMetadata
{
    /**
     * @psalm-param list<class-string> $types
     */
    public function __construct(
        public readonly string $name,
        protected array $types,
        public readonly AssociationResolver $associationResolver,
        public readonly SagaCreationPolicy $creationPolicy = SagaCreationPolicy::NONE,
        public readonly bool $end = false,
    ) {
    }

    /**
     * @psalm-return list<class-string>
     */
    public function getTypes(): array
    {
        return $this->types;
    }
}
