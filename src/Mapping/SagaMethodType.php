<?php

declare(strict_types=1);

namespace Brzuchal\Saga\Mapping;

enum SagaMethodType: string
{
    case Start = 'start';
    case End = 'end';
    case Default = 'default';
}
