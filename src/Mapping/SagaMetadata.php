<?php declare(strict_types=1);

namespace Brzuchal\Saga\Mapping;

use Brzuchal\Saga\Association\AssociationValue;
use Closure;

final class SagaMetadata
{
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
            \assert($method instanceof SagaMethodMetadata);
            if (!\in_array($class, $method->getTypes(), true)) {
                continue;
            }

            return $method;
        }

        return null;
    }
}
