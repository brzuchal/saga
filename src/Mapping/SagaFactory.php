<?php

declare(strict_types=1);

namespace Brzuchal\Saga\Mapping;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class SagaFactory
{
    /**
     * @param class-string|null $class
     */
    public function __construct(
        public readonly string|null $class = null,
    ) {
    }
}
