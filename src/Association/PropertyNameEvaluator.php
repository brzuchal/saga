<?php declare(strict_types=1);

namespace Brzuchal\Saga\Association;

use Stringable;

final class PropertyNameEvaluator implements AssociationEvaluator
{
    public function __construct(
        protected string $propertyName,
    ) {
    }

    public function evaluate(object $object): string|Stringable
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

    public function supports(string $type, string $key): bool
    {
        return \property_exists($type, $this->propertyName);
    }
}
