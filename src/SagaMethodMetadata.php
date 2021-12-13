<?php declare(strict_types=1);

namespace Brzuchal\Saga;

use Brzuchal\Saga\Association\AssociationResolver;

final class SagaMethodMetadata
{
    /**
     * @param list<class-string> $parameterTypes
     */
    public function __construct(
        protected string $name,
        /** @psalm-var list<class-string> */
        protected array $parameterTypes,
        protected AssociationResolver $associationResolver,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @psalm-return list<class-string>
     */
    public function getParameterTypes(): array
    {
        return self::listTypes($this->parameterTypes);
    }

    public function getAssociationResolver(): AssociationResolver
    {
        return $this->associationResolver;
    }

    /**
     * @psalm-param list<class-string>
     * @psalm-return list<class-string>
     */
    public static function listTypes(array $classes): array
    {
        $types = [];
        foreach ($classes as $class) {
            $types += [$class => $class]
                + \class_parents($class)
                + \class_implements($class);
        }

        return $types;
    }
}
