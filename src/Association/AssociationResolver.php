<?php

declare(strict_types=1);

namespace Brzuchal\Saga\Association;

final class AssociationResolver
{
    public function __construct(
        private string $key,
        private AssociationEvaluator $associationEvaluator,
    ) {
    }

    public function resolve(object $message): AssociationValue|null
    {
        if (! $this->associationEvaluator->supports($message::class, $this->key)) {
            throw new \UnexpectedValueException(\sprintf(
                'Class %s is not supported by given association evaluator',
                $message::class,
            ));
        }

        $value = $this->associationEvaluator->evaluate($message);

        return new AssociationValue($this->key, $value);
    }
}
