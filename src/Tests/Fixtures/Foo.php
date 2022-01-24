<?php declare(strict_types=1);

namespace Brzuchal\Saga\Tests\Fixtures;

use Brzuchal\Saga\SagaLifecycle;

class Foo
{
    public bool $fooInvoked = false;
    public bool $barInvoked = false;
    public bool $bazInvoked = false;

    public function foo(FooMessage $message, SagaLifecycle $lifecycle): void
    {
        $this->fooInvoked = true;
        $lifecycle->associateWith('str', 'bar');
    }

    public function bar(BarMessage $message, SagaLifecycle $lifecycle): void
    {
        $this->barInvoked = true;
        if ($message->exception === null) {
            return;
        }

        $lifecycle->reject($message->exception);
    }

    public function baz(BazMessage $message): void
    {
        $this->bazInvoked = true;
        if ($message->exception === null) {
            return;
        }

        throw $message->exception;
    }
}
