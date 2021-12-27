<?php declare(strict_types=1);

namespace Brzuchal\Saga\Tests\Fixtures;

class Foo
{
    public bool $fooInvoked = false;
    public bool $barInvoked = false;

    public function foo(FooMessage $message): void
    {
        $this->fooInvoked = true;
    }

    public function bar(BarMessage $message): void
    {
        $this->barInvoked = true;
    }
}
