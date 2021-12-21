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
class SagaInitializationPolicy
{
    /**
     * Creates an instance using the given {@see self::creationPolicy()} and {@see self::initialAssociationValue()}.
     *
     * @param SagaCreationPolicy $creationPolicy             The policy describing the condition to loadMetadataForClass a new instance
     * @param AssociationValue $initialAssociationValue The association value a new Saga instance should be given
     */
    public function __construct(
        private SagaCreationPolicy $creationPolicy,
        private AssociationValue $initialAssociationValue,
    ) {
    }

    /**
     * Returns the creation policy
     *
     * @return SagaCreationPolicy the creation policy
     */
    public function getCreationPolicy(): SagaCreationPolicy
    {
        return $this->creationPolicy;
    }

    /**
     * Returns the initial association value for a newly created saga. May be {@code null}.
     *
     * @return AssociationValue the initial association value for a newly created saga
     */
    public function getInitialAssociationValue(): AssociationValue
    {
        return $this->initialAssociationValue;
    }
}
