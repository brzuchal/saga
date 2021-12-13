<?php declare(strict_types=1);

namespace Brzuchal\Saga\Tests\Fixtures;

class Foo
{
    public bool $fooInvoked = false;

    public function foo(FooMessage $message): void
    {
        $this->fooInvoked = true;
    }
}
