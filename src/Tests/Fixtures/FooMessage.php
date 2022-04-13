<?php

declare(strict_types=1);

namespace Brzuchal\Saga\Tests\Fixtures;

class FooMessage
{
    public function __construct(
        public string $id = 'ad96ec16-b420-4f56-8720-b41cdbbe9569',
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }
}
