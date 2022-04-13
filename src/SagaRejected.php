<?php

declare(strict_types=1);

namespace Brzuchal\Saga;

use Exception;

class SagaRejected extends Exception
{
    public static function create(\Throwable|null $exception): self
    {
        return new self('Saga rejection called by method', previous: $exception);
    }
}
