<?php

declare(strict_types=1);

namespace Brzuchal\Saga\Association;

use RuntimeException;
use Stringable;

final class MethodNameEvaluator implements AssociationEvaluator
{
    public function __construct(
        protected string $methodName,
    ) {
    }

    public function evaluate(object $object): string|Stringable
    {
        if (\method_exists($object, $this->methodName)) {
            return $object->{$this->methodName}();
        }

        throw new RuntimeException(\sprintf(
            'Unable to evaluate method %s on %s',
            $this->methodName,
            $object::class
        ));
    }

    /**
     * @psalm-param class-string $type
     */
    public function supports(string $type, string $key): bool
    {
        return \method_exists($type, $this->methodName);
    }
}
