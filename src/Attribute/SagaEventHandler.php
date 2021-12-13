<?php declare(strict_types=1);

namespace Brzuchal\Saga\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class SagaEventHandler
{
    public string $associationKey;

    /**
     * @psalm-param array|null $expressionParameters
     */
    public function __construct(
        string|null $associationKey = null,
        public string|null $property = null,
        public string|null $method = null,
        public string|null $expression = null,
        /** @psalm-var array|null */
        public array|null $expressionParameters = [],
        /** @psalm-var class-string|null */
        public string|null $evaluator = null,
    ) {
        if ($this->property === null && $associationKey === null) {
            throw new \RuntimeException(
                'Association key has to be passed if no association property given'
            );
        }

        $this->associationKey = $associationKey ?? $this->property;
    }
}
