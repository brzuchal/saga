<?php declare(strict_types=1);

namespace Brzuchal\Saga;

use Brzuchal\Saga\Association\AssociationValue;
use Exception;

final class SagaInstanceNotFound extends Exception
{
    /**
     * @param class-string $type
     */
    public static function unableToLoad(string $type, string $identifier): self
    {
        return new self(\sprintf(
            'Instance of %s Saga identified by %s',
            $type,
            $identifier,
        ));
    }
}
