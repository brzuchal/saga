<?php declare(strict_types=1);

namespace Brzuchal\Saga;

use Brzuchal\Saga\Association\AssociationValue;
use Exception;

final class SagaInstanceNotFound extends Exception
{
    /**
     * @param class-string $type
     */
    public static function unableToLoad(string $type, AssociationValue $associationValue): self
    {
        return new self(\sprintf(
            'Instance of %s Saga not found using association %s=%s',
            $type,
            $associationValue->getKey(),
            (string) $associationValue->getValue(),
        ));
    }
}
