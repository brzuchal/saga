<?php declare(strict_types=1);

namespace Brzuchal\Saga\Tests\Fixtures;

use Brzuchal\Saga\Attribute\Saga;
use Brzuchal\Saga\Attribute\SagaEventHandler;
use Brzuchal\Saga\Attribute\SagaStart;

#[Saga]
class AttributedFoo
{
    #[SagaStart,SagaEventHandler(associationKey: 'keyId', property: 'id')]
    public function foo(FooMessage|FooBarMessage $message): void
    {
    }
}
