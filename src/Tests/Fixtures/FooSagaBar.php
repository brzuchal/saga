<?php

declare(strict_types=1);

namespace Brzuchal\Saga\Tests\Fixtures;

class FooSagaBar extends FooSaga
{
    public function fooBar(FooBarMessage $message): void
    {
    }
}
