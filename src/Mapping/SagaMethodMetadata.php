<?php declare(strict_types=1);

namespace Brzuchal\Saga\Mapping;

use Brzuchal\Saga\Association\AssociationResolver;
use function class_implements;
use function class_parents;

final class SagaMethodMetadata
{
    public function __construct(
        protected string $name,
        /** @psalm-var list<class-string> */
        protected array $types,
        protected AssociationResolver $associationResolver,
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
        $types = [];
        foreach ($this->types as $class) {
            $types += [$class => $class]
                + class_parents($class)
                + class_implements($class);
        }

        return $types;
    }

}
