<?php declare(strict_types=1);

namespace Brzuchal\Saga\Mapping;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class SagaMessageHandler
{
    public string $key;

    /**
     * @psalm-param array|null $expressionParameters
     */
    public function __construct(
        string|null $key = null,
        public string|null $property = null,
        public string|null $method = null,
        public string|null $expression = null,
        /** @psalm-var array|null */
        public array|null $expressionParameters = [],
        /** @psalm-var class-string|null */
        public string|null $evaluator = null,
    ) {
        if ($this->property === null && $key === null) {
            throw new \RuntimeException(
                'Association key has to be passed if no association property given'
            );
        }

        $this->key = $key ?? $this->property;
    }
}
