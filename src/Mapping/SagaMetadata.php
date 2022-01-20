<?php declare(strict_types=1);

namespace Brzuchal\Saga\Mapping;

use Brzuchal\Saga\Association\AssociationValue;
use Brzuchal\Saga\SagaCreationPolicy;
use Closure;

final class SagaMetadata
{
    public function __construct(
        /** @psalm-var class-string */
        public readonly string $type,
        protected readonly Closure $factory,
        /** @psalm-var list<SagaMethodMetadata> */
        protected array $methods,
    ) {
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
            throw IncompleteSagaMetadata::unsupportedMessageType($this->type, $class);
        }

        $associationValue = $methodMetadata->associationResolver->resolve($message);
        if ($associationValue === null) {
            throw IncompleteSagaMetadata::missingAssociationResolver($this->type, $class);
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
            throw IncompleteSagaMetadata::unsupportedMessageType($this->type, $class);
        }

        return $methodMetadata->name;
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

    /**
     * @throws IncompleteSagaMetadata
     */
    public function creationPolicy(object $message): SagaCreationPolicy
    {
        $messageType = \get_class($message);
        $metadata = $this->findForArgumentType($messageType);
        if ($metadata === null) {
            throw IncompleteSagaMetadata::unsupportedMessageType($this->type, $messageType);
        }

        return $metadata->creationPolicy;
    }

    /**
     * @throws IncompleteSagaMetadata
     */
    public function isCompleting(object $message): bool
    {
        $messageType = \get_class($message);
        $metadata = $this->findForArgumentType($messageType);
        if ($metadata === null) {
            throw IncompleteSagaMetadata::unsupportedMessageType($this->type, $messageType);
        }

        return $metadata->end;
    }
}
