<?php declare(strict_types=1);

namespace Brzuchal\Saga\Mapping;

use Brzuchal\Saga\Association\AssociationValue;
use Brzuchal\Saga\SagaCreationPolicy;
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

    /**
     * @throws IncompleteSagaMetadata
     */
    public function resolveAssociation(object $message): AssociationValue
    {
        $class = \get_class($message);
        $methodMetadata = $this->findForArgumentType($class);
        if ($methodMetadata === null) {
            throw IncompleteSagaMetadata::unsupportedMessageType($this->getName(), $class);
        }

        $associationValue = $methodMetadata->getAssociationResolver()->resolve($message);
        if ($associationValue === null) {
            throw IncompleteSagaMetadata::missingAssociationResolver($this->getName(), $class);
        }

        return $associationValue;
    }

    /**
     * @throws IncompleteSagaMetadata
     */
    public function findHandlerMethod(object $message): string
    {
        $class = \get_class($message);
        $methodMetadata = $this->findForArgumentType($class);
        if ($methodMetadata === null) {
            throw IncompleteSagaMetadata::unsupportedMessageType($this->getName(), $class);
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
    protected function findForArgumentType(string $class): SagaMethodMetadata|null
    {
        foreach ($this->methods as $method) {
            if (!\in_array($class, $method->getTypes(), true)) {
                continue;
            }

            return $method;
        }

        return null;
    }

    public function getSagaCreationPolicy(object $message): SagaCreationPolicy
    {
        $metadata = $this->findForArgumentType(\get_class($message));
        if ($metadata === null) {
            return SagaCreationPolicy::NONE;
        }

        return $metadata->getCreationPolicy();
    }
}
