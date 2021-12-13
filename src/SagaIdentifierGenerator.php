<?php declare(strict_types=1);

namespace Brzuchal\Saga;

use Exception;

class SagaIdentifierGenerator
{
    /**
     * @throws Exception
     */
    public function generateIdentifier(): string
    {
        return \hash('sha256', \random_bytes(1024));
    }
}
