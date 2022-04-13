<?php

declare(strict_types=1);

namespace Brzuchal\Saga;

use Exception;

class SagaIdentifierGenerator
{
    /**
     * @throws IdentifierGenerationFailed
     */
    public function generateIdentifier(): string
    {
        try {
            return \hash('sha256', \random_bytes(1024));
        } catch (\Throwable $exception) {
            throw IdentifierGenerationFailed::forAlgoAndException('sha256', $exception);
        }
    }
}
