<?php declare(strict_types=1);

namespace Brzuchal\Saga;

use Brzuchal\Saga\Association\AssociationValue;

/**
 * Based on SagaInitializationPolicy from AxonFramework
 * @author Allard Buijze
 */

/**
 * Describes the conditions under which a Saga should be created, and which {@see AssociationValue}
 * it should be initialized with.
 */
final class SagaInitializationPolicy
{
    /**
     * Creates an instance using the given {@see self::creationPolicy()} and {@see self::initialAssociationValue()}.
     *
     * @param SagaCreationPolicy $creationPolicy             The policy describing the condition to loadMetadataForClass a new instance
     * @param AssociationValue $initialAssociationValue The association value a new Saga instance should be given
     */
    public function __construct(
        protected SagaCreationPolicy $creationPolicy,
        protected AssociationValue $initialAssociationValue,
    ) {
    }

    public function createAlways(): bool
    {
        return $this->creationPolicy === SagaCreationPolicy::ALWAYS;
    }

    public function createIfNoneFound(): bool
    {
        return $this->creationPolicy === SagaCreationPolicy::IF_NONE_FOUND;
    }

    public function initialAssociationValue(): AssociationValue
    {
        return $this->initialAssociationValue;
    }
}
