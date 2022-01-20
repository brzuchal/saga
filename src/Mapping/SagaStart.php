<?php declare(strict_types=1);

namespace Brzuchal\Saga\Mapping;

use Attribute;

/**
 * Indicates that the saga method can trigger the creation of a new Saga instance.
 *
 * When a Saga is started due to an invocation on a method with SagaStart attribute, the association of the method
 * and the actual property's value are used to define a AssociationValue for the created saga.
 *
 * This annotation can only appear on methods that have {@link SagaMessageHandler} attribute.
 */
#[Attribute(Attribute::TARGET_METHOD)]
class SagaStart
{
    public function __construct(
        public readonly string|null $dateTimeProperty = null,
        public readonly bool $forceNew = false,
    ) {
    }
}
