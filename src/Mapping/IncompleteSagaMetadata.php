<?php

declare(strict_types=1);

namespace Brzuchal\Saga\Mapping;

use Exception;

final class IncompleteSagaMetadata extends Exception
{
    /**
     * @psalm-param class-string $sagaClass
     * @psalm-param class-string $messageClass
     */
    public static function unsupportedMessageType(string $sagaClass, string $messageClass): self
    {
        return new self(\sprintf(
            'Method metadata for message of type %s not found in %s',
            $messageClass,
            $sagaClass,
        ));
    }

    /**
     * @psalm-param class-string $sagaClass
     * @psalm-param class-string $messageClass
     */
    public static function missingAssociationResolver(string $sagaClass, string $messageClass): self
    {
        return new self(\sprintf(
            'Method metadata for message of type %s found in %s is missing association resolving info',
            $messageClass,
            $sagaClass,
        ));
    }

    /**
     * @psalm-param class-string $sagaClass
     * @psalm-param string $methodName
     */
    public static function cannotDetermineMessageType(string $sagaClass, string $methodName): self
    {
        return new self(\sprintf(
            'Saga methods require at least one required parameter, none given in %s::%s',
            $sagaClass,
            $methodName,
        ));
    }
}
