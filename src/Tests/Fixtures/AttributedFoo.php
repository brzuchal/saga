<?php declare(strict_types=1);

namespace Brzuchal\Saga\Tests\Fixtures;

use Brzuchal\Saga\Mapping\Saga;
use Brzuchal\Saga\Mapping\SagaEnd;
use Brzuchal\Saga\Mapping\SagaMessageHandler;
use Brzuchal\Saga\Mapping\SagaStart;
use Brzuchal\Saga\SagaLifecycle;

#[Saga]
class AttributedFoo
{
    public ?string $foo = null;
    public bool $fooInvoked = false;
    public ?string $bar = null;
    public bool $barInvoked = false;
    public ?string $baz = null;
    public bool $bazInvoked = false;

    #[SagaStart,SagaMessageHandler(key: 'keyInt', property: 'id')]
    public function foo(FooMessage|FooBarMessage $message): void
    {
        $this->foo = $message->id;
        $this->fooInvoked = true;
    }

    #[SagaMessageHandler(key: 'keyString', property: 'bar')]
    public function bar(BarMessage $message, SagaLifecycle $lifecycle): void
    {
        $this->bar = $message->bar;
        $this->barInvoked = true;
    }

    #[SagaEnd,SagaMessageHandler(key: 'keyInt', property: 'id')]
    public function baz(BazMessage $message, SagaLifecycle $lifecycle): void
    {
        $this->baz = $message->id;
        $this->bazInvoked = true;
    }
}
