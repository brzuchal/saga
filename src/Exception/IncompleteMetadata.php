<?php declare(strict_types=1);

namespace Brzuchal\Saga\Exception;

use Exception;

final class IncompleteMetadata extends Exception
{
    public static function unsupportedMessageType(string $sagaClass, string $messageClass): self
    {
        return new self(\sprintf(
            'Method metadata for message of type %s not found in %s',
            $messageClass,
            $sagaClass,
        ));
    }
}
