<?php declare(strict_types=1);

namespace Brzuchal\Saga\Tests\Fixtures;

class FooBarMessage
{
    public string $id = 'f10905bd-b805-45d1-8d43-3c4cb782e9f3';

    public function getId(): string
    {
        return $this->id;
    }
}
