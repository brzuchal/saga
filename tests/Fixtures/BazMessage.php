<?php declare(strict_types=1);

namespace Brzuchal\Saga\Tests\Fixtures;

class BazMessage
{
    public function __construct(
        public int $id = 123,
        public readonly array $baz = [true],
    ) {
    }
}
