<?php declare(strict_types=1);

namespace Brzuchal\Saga\Tests\Fixtures;

class FooMessage
{
    public function __construct(
        public int $id = 123,
    ) {}

    public function getId(): int
    {
        return $this->id;
    }
}
