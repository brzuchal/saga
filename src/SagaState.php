<?php declare(strict_types=1);

namespace Brzuchal\Saga;

enum SagaState: int
{
    case Pending = 0;
    case Completed = 1;
    case Rejected = -1;
}
