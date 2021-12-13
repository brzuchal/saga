<?php declare(strict_types=1);

namespace Brzuchal\Saga\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final class Saga
{
    public function __construct(
        /** @psalm-param null|class-string */
        public ?string $store = null,
        /** @psalm-param null|class-string */
        public ?string $factory = null,
    ) {
    }
}
