<?php declare(strict_types=1);

namespace Brzuchal\Saga\Tests\Fixtures;

use Exception;

class BazMessage
{
    public function __construct(
        public string $id = '1ae44be1-3a6a-4167-9b22-bc1bef49808a',
        public readonly Exception|null $exception = null,
    ) {
    }
}
