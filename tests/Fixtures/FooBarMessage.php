<?php declare(strict_types=1);

namespace Brzuchal\Saga\Tests\Fixtures;

class FooBarMessage
{
    public int $id = 456;

    public function getId(): int
    {
        return $this->id;
    }
}
