<?php declare(strict_types=1);

namespace Brzuchal\Saga\Tests\Fixtures;

class BarMessage
{
    public function __construct(
        public readonly string $bar = 'bar',
    ) {
    }
}
