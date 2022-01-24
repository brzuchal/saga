<?php declare(strict_types=1);

namespace Brzuchal\Saga\Tests\Fixtures;

class FooBar extends Foo
{
    public function fooBar(FooBarMessage $message): void
    {
    }
}
