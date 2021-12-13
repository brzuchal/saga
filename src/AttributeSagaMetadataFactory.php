<?php declare(strict_types=1);

namespace Brzuchal\Saga;

use Brzuchal\Saga\Association\AssociationResolver;
use Brzuchal\Saga\Association\PropertyNameEvaluator;
use Brzuchal\Saga\Attribute\SagaEventHandler;
use Brzuchal\Saga\Attribute\SagaStart;
use Brzuchal\Saga\Factory\ReflectionClassFactory;
use Closure;
use ReflectionMethod as CoreReflectionMethod;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Roave\BetterReflection\Reflection\ReflectionIntersectionType;
use Roave\BetterReflection\Reflection\ReflectionMethod;
use Roave\BetterReflection\Reflection\ReflectionNamedType;
use Roave\BetterReflection\Reflection\ReflectionUnionType;
use UnexpectedValueException;

class AttributeSagaMetadataFactory implements SagaMetadataFactory
{
    const METHODS_FILTER = CoreReflectionMethod::IS_PUBLIC ^ CoreReflectionMethod::IS_ABSTRACT ^ CoreReflectionMethod::IS_STATIC;

    /** @inheritdoc */
    public function create(string $class): SagaMetadata
    {
        // TODO: rework
        \assert(\class_exists($class));
        $reflection = ReflectionClass::createFromName($class);
        $factory = new ReflectionClassFactory($reflection->getName());
        \assert(\is_callable($factory));

        return new SagaMetadata(
            $class,
            Closure::fromCallable($factory),
            $this->extractMethods($reflection),
        );
    }

    /**
     * @psalm-return list<SagaMethodMetadata>
     */
    public function extractMethods(ReflectionClass $class): array
    {
        $methods = [];
        foreach ($class->getMethods(self::METHODS_FILTER) as $method) {
            $eventHandlerAttribute = $this->extractEventHandlerAttribute($method);
            if ($eventHandlerAttribute === null) {
                continue;
            }
            if ($method->getNumberOfRequiredParameters() < 1) {
                throw new UnexpectedValueException(\sprintf(
                    'Saga methods require at least one required parameter, none given in %s::%s',
                    $class->getName(),
                    $method->getName(),
                ));
            }
            $parameterTypes = $this->extractMethodParameterTypes($method);
            $startAttribute = $this->extractStartAttribute($method);
            $startMethod = $startAttribute instanceof SagaStart;
            $forceNew = false;
            if ($startAttribute) {
                $forceNew = $startAttribute->forceNew;
            }

            $methods[] = new SagaMethodMetadata(
                name: $method->getName(),
                parameterTypes: $parameterTypes,
                associationResolver: new AssociationResolver(
                    'id',
                    new PropertyNameEvaluator('id'),
                ),
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

        $instance = new ($attributes[0]->getName())(...$attributes[0]->getArguments());
        \assert($instance instanceof SagaStart);

        return $instance;
    }

    private function extractEventHandlerAttribute(ReflectionMethod $method): SagaEventHandler|null
    {
        $attributes = $method->getAttributesByName(SagaEventHandler::class);
        if (empty($attributes)) {
            return null;
        }

        $instance = new ($attributes[0]->getName())(...$attributes[0]->getArguments());
        \assert($instance instanceof SagaEventHandler);

        return $instance;
    }

    /**
     * @psalm-return list<class-string>
     */
    private function extractMethodParameterTypes(ReflectionMethod $method): array
    {
        $parameter = $method->getParameters()[0];
        if (!$parameter->hasType()) {
            throw new \UnexpectedValueException('Saga methods require typed first argument');
        }
        if ($parameter->allowsNull()) {
            throw new \UnexpectedValueException('Saga methods require non-nullable first argument');
        }
        if ($parameter->isDefaultValueAvailable()) {
            throw new \UnexpectedValueException('Saga method first argument default value is forbidden');
        }
        $parameterType = $parameter->getType();
        if ($parameterType === null) {
            throw new UnexpectedValueException('Saga method first argument type is required');
        }
        if ($parameterType instanceof ReflectionIntersectionType) {
            throw new \UnexpectedValueException('Saga method first argument type cannot be intersection type');
        }
        if ($parameterType instanceof ReflectionNamedType) {
            if ($parameterType->isBuiltin()) {
                throw new \UnexpectedValueException('Saga method first argument type cannot be built-in');
            }
            $type = $parameterType->getName();
            \assert(\class_exists($type));

            return [$type];
        }
        $types = [];
        if ($parameterType->getTypes()) {
            foreach ($parameterType->getTypes() as $type) {
                if ($type->isBuiltin()) {
                    throw new \UnexpectedValueException('Saga method first argument type cannot include built-in types');
                }
                if ($type->allowsNull()) {
                    throw new \UnexpectedValueException('Saga methods first argument type cannot include null type');
                }
                $type = $type->getName();
                \assert(\class_exists($type));
                $types[] = $type;
            }
        }

        return $types;
    }
}
