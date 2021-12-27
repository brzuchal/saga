<?php declare(strict_types=1);

namespace Brzuchal\Saga;

use Exception;

final class IdentifierGenerationFailed extends Exception
{
    public static function forAlgoAndException(string $algo, Exception $exception): self
    {
        return new self(\sprintf(
            'Unable to generate identifier using hash algo %s',
            $algo,
        ), previous: $exception);
    }
}
