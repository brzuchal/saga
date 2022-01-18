<?php declare(strict_types=1);

namespace Brzuchal\Saga\Tests\Fixtures;

use Exception;

class BazMessage
{
    public function __construct(
        public int $id = 123,
        public readonly Exception|null $exception = null,
    ) {
    }
}
