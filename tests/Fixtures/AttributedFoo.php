<?php declare(strict_types=1);

namespace Brzuchal\Saga\Tests\Fixtures;

use Brzuchal\Saga\Mapping\Saga;
use Brzuchal\Saga\Mapping\SagaEnd;
use Brzuchal\Saga\Mapping\SagaEventHandler;
use Brzuchal\Saga\Mapping\SagaStart;
use Brzuchal\Saga\SagaLifecycle;

#[Saga]
class AttributedFoo
{
    public ?int $foo = null;
    public bool $fooInvoked = false;
    public ?string $bar = null;
    public bool $barInvoked = false;
    public ?array $baz = null;
    public bool $bazInvoked = false;

    #[SagaStart,SagaEventHandler(associationKey: 'keyInt', property: 'id')]
    public function foo(FooMessage|FooBarMessage $message): void
    {
        $this->foo = $message->id;
        $this->fooInvoked = true;
    }

    #[SagaEventHandler(associationKey: 'keyString', property: 'bar')]
    public function bar(BarMessage $message, SagaLifecycle $lifecycle): void
    {
        $this->bar = $message->bar;
        $this->barInvoked = true;
    }

    #[SagaEnd,SagaEventHandler(associationKey: 'intInt', property: 'id')]
    public function baz(BazMessage $message, SagaLifecycle $lifecycle): void
    {
        $this->baz = $message->baz;
        $this->bazInvoked = true;
    }
}
