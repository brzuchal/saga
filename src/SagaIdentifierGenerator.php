<?php

declare(strict_types=1);

namespace Brzuchal\Saga;

use Ramsey\Uuid\Uuid;

class SagaIdentifierGenerator
{
    public function generateIdentifier(): string
    {
        return Uuid::uuid4()->toString();
    }
}
