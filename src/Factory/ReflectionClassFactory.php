<?php declare(strict_types=1);

namespace Brzuchal\Saga\Factory;

use ReflectionClass;
use ReflectionException;

/**
 * @template T
 */
final class ReflectionClassFactory
{
    private ReflectionClass $reflectionClass;

    /**
     * @param class-string<T> $type
     * @throws ReflectionException
     */
    public function __construct(
        protected string $type,
    ) {
        $this->reflectionClass = new ReflectionClass($type);
    }

    /**
     * @psalm-return T
     * @throws ReflectionException
     */
    public function __invoke(): callable
    {
        $instance = $this->reflectionClass->newInstanceWithoutConstructor();
        \assert($instance instanceof $this->type);

        return $instance;
    }
}
