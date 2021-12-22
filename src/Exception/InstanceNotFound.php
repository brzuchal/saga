<?php declare(strict_types=1);

namespace Brzuchal\Saga\Exception;

use Brzuchal\Saga\Association\AssociationValue;
use DomainException;

final class InstanceNotFound extends DomainException
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
