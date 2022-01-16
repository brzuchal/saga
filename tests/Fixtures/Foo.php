<?php declare(strict_types=1);

namespace Brzuchal\Saga\Tests\Fixtures;

use Brzuchal\Saga\SagaLifecycle;

class Foo
{
    public bool $fooInvoked = false;
    public bool $barInvoked = false;

    public function foo(FooMessage $message, SagaLifecycle $lifecycle): void
    {
        $this->fooInvoked = true;
        $lifecycle->associateValue('str', 'bar');
    }

    public function bar(BarMessage $message): void
    {
        $this->barInvoked = true;
    }
}
