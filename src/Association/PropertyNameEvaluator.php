<?php declare(strict_types=1);

namespace Brzuchal\Saga\Association;

use Symfony\Component\PropertyAccess\PropertyPath;

final class PropertyNameEvaluator implements AssociationEvaluator
{
    public function __construct(
        protected string $propertyName,
    ) {
    }

    public function evaluate(object $object): string | int
    {
        if (\property_exists($object, $this->propertyName)) {
            return $object->{$this->propertyName};
        }

        throw new \RuntimeException(\sprintf(
            'Unable to evaluate property %s from %s',
            $this->propertyName,
            \get_class($object)
        ));
    }

    public function supports(string $type, string $associationKey): bool
    {
        return \property_exists($type, $this->propertyName);
    }
}
