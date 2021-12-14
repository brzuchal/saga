<?php declare(strict_types=1);

namespace Brzuchal\Saga\Mapping;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class SagaStart
{
    public function __construct(
        public string|null $dateTimeProperty = null,
        public bool $forceNew = false,
    ) {
    }
}
