<?php declare(strict_types=1);

namespace Brzuchal\Saga\Mapping;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class SagaCompensate
{
    public function __construct(
        public string $action
    ) {
    }
}
