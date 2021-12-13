<?php declare(strict_types=1);

namespace Brzuchal\Saga;

use Brzuchal\Saga\Association\AssociationValue;
use Closure;

final class SagaMetadata
{
    /**
     * @psalm-param list<SagaMethodMetadata> $methods
     */
    public function __construct(
        /** @psalm-var class-string */
        protected string $type,
        protected Closure $factory,
        /** @psalm-var list<SagaMethodMetadata> */
        protected array $methods,
    ) {
    }

    /**
     * @psalm-return class-string
     */
    public function getName(): string
    {
        \assert(\class_exists($this->type));

        return $this->type;
    }

    public function newInstance(): object
    {
        return ($this->factory)();
    }

    public function resolveAssociation(object $message): ?AssociationValue
    {
        return $this->findForArgumentType(\get_class($message))
            ?->getAssociationResolver()
            ->resolve($message);
    }

    public function findHandlerMethod(object $message): string
    {
        $methodMetadata = $this->findForArgumentType(\get_class($message));
        if ($methodMetadata === null) {
            throw new \UnexpectedValueException('Handler method not found');
        }

        return $methodMetadata->getName();
    }

    public function hasHandlerMethod(object $message): bool
    {
        return $this->findForArgumentType(\get_class($message)) !== null;
    }

    /**
     * @psalm-param class-string $class
     */
    protected function findForArgumentType(string $class): ?SagaMethodMetadata
    {
        foreach ($this->methods as $method) {
            if (!\in_array($class, $method->getParameterTypes(), true)) {
                continue;
            }

            return $method;
        }

        return null;
    }
}
