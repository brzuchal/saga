<?php

declare(strict_types=1);

namespace Brzuchal\Saga\Mapping;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final class Saga
{
    public function __construct(
        /** @psalm-param null|class-string */
        public readonly string|null $store = null,
        /** @psalm-param null|class-string */
        public readonly string|null $factory = null,
    ) {
    }
}
