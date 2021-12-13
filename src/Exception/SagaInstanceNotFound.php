<?php declare(strict_types=1);

namespace Brzuchal\Saga\Exception;

use Brzuchal\Saga\Association\AssociationValue;
use DomainException;

final class SagaInstanceNotFound extends DomainException
{
    /**
     * @param class-string $type
     */
    public function __construct(string $type, AssociationValue $associationValue)
    {
        $this->message = \sprintf(
            'Instance of %s Saga not found using association %s=%s',
            $type,
            $associationValue->getKey(),
            (string) $associationValue->getValue(),
        );
    }
}
