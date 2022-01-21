<?php declare(strict_types=1);

namespace Brzuchal\Saga\Mapping;

use Brzuchal\Saga\Association\AssociationResolver;
use Brzuchal\Saga\Association\PropertyNameEvaluator;
use Brzuchal\Saga\Factory\ReflectionClassFactory;
use Brzuchal\Saga\SagaCreationPolicy;
use ReflectionException;
use ReflectionMethod as CoreReflectionMethod;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Roave\BetterReflection\Reflection\ReflectionIntersectionType;
use Roave\BetterReflection\Reflection\ReflectionMethod;
use Roave\BetterReflection\Reflection\ReflectionNamedType;
use Roave\BetterReflection\Reflector\Exception\IdentifierNotFound;
use UnexpectedValueException;

final class AttributeMappingDriver implements MappingDriver
{
    private const METHODS_FILTER = CoreReflectionMethod::IS_PUBLIC ^ CoreReflectionMethod::IS_ABSTRACT ^ CoreReflectionMethod::IS_STATIC;

    /**
     * @inheritdoc
     * @throws ReflectionException
     * @throws IncompleteSagaMetadata
     */
    public function loadMetadataForClass(string $class): SagaMetadata|null
    {
        try {
            $reflection = ReflectionClass::createFromName($class);
        } catch (IdentifierNotFound) {
            return null;
        }

        $factory = new ReflectionClassFactory($reflection->getName());

        return new SagaMetadata(
            $class,
            $factory(...),
            $this->extractMethods($reflection),
        );
    }

    /**
     * @psalm-return list<SagaMethodMetadata>
     * @throws IncompleteSagaMetadata
     */
    public function extractMethods(ReflectionClass $class): array
    {
        $methods = [];
        foreach ($class->getMethods(self::METHODS_FILTER) as $method) {
            $eventHandlerAttribute = $this->extractMessageHandlerAttribute($method);
            if ($eventHandlerAttribute === null) {
                continue;
            }
            if ($method->getNumberOfRequiredParameters() < 1) {
                throw IncompleteSagaMetadata::cannotDetermineMessageType(
                    $class->getName(),
                    $method->getName(),
                );
            }
            $parameterTypes = $this->extractMethodParameterTypes($method);
            $creationPolicy = $this->extractCreationPolicy($method);

            // TODO: replace with use of factory of association value evaluator
            \assert($eventHandlerAttribute->property !== null);
            $methods[] = new SagaMethodMetadata(
                name: $method->getName(),
                types: $parameterTypes,
                associationResolver: new AssociationResolver(
                    $eventHandlerAttribute->key,
                    new PropertyNameEvaluator($eventHandlerAttribute->property),
                ),
                creationPolicy: $creationPolicy,
                end: $this->hasEndAttribute($method),
            );
        }

        return $methods;
    }

    private function extractStartAttribute(ReflectionMethod $method): SagaStart|null
    {
        $attributes = $method->getAttributesByInstance(SagaStart::class);
        if (empty($attributes)) {
            return null;
        }

        $className = $attributes[0]->getName();
        \assert(\class_exists($className));
        $instance = new ($className)(...$attributes[0]->getArguments());
        \assert($instance instanceof SagaStart);

        return $instance;
    }

    private function extractMessageHandlerAttribute(ReflectionMethod $method): SagaMessageHandler|null
    {
        $attributes = $method->getAttributesByName(SagaMessageHandler::class);
        if (empty($attributes)) {
            return null;
        }

        $className = $attributes[0]->getName();
        \assert(\class_exists($className));
        $instance = new ($className)(...$attributes[0]->getArguments());
        \assert($instance instanceof SagaMessageHandler);

        return $instance;
    }

    /**
     * @psalm-return list<class-string>
     */
    private function extractMethodParameterTypes(ReflectionMethod $method): array
    {
        $parameter = $method->getParameters()[0];
        if (!$parameter->hasType()) {
            throw new UnexpectedValueException('Saga methods require typed first argument');
        }
        if ($parameter->allowsNull()) {
            throw new UnexpectedValueException('Saga methods require non-nullable first argument');
        }
        if ($parameter->isDefaultValueAvailable()) {
            throw new UnexpectedValueException('Saga method first argument default value is forbidden');
        }
        $parameterType = $parameter->getType();
        if ($parameterType === null) {
            throw new UnexpectedValueException('Saga method first argument type is required');
        }
        if ($parameterType instanceof ReflectionIntersectionType) {
            throw new UnexpectedValueException('Saga method first argument type cannot be intersection type');
        }
        if ($parameterType instanceof ReflectionNamedType) {
            if ($parameterType->isBuiltin()) {
                throw new UnexpectedValueException('Saga method first argument type cannot be built-in');
            }
            $type = $parameterType->getName();
            \assert(\class_exists($type));

            return [$type];
        }
        $types = [];
        if ($parameterType->getTypes()) {
            foreach ($parameterType->getTypes() as $type) {
                if ($type->isBuiltin()) {
                    throw new UnexpectedValueException('Saga method first argument type cannot include built-in types');
                }
                if ($type->allowsNull()) {
                    throw new UnexpectedValueException('Saga methods first argument type cannot include null type');
                }
                $type = $type->getName();
                \assert(\class_exists($type));
                $types[] = $type;
            }
        }

        return $types;
    }

    protected function extractCreationPolicy(ReflectionMethod $method): SagaCreationPolicy
    {
        $startAttribute = $this->extractStartAttribute($method);
        if ($startAttribute === null) {
            return SagaCreationPolicy::NONE;
        }

        if ($startAttribute->forceNew) {
            return SagaCreationPolicy::ALWAYS;
        }

        return SagaCreationPolicy::IF_NONE_FOUND;
    }

    protected function hasEndAttribute(ReflectionMethod $method): bool
    {
        $attributes = $method->getAttributesByInstance(SagaStart::class);
        if (empty($attributes)) {
            return false;
        }

        return true;
    }
}
