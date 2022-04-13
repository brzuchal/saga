<?php

declare(strict_types=1);

namespace Brzuchal\Saga\Store;

interface SetupableSagaStore
{
    public function setup(): void;
}
