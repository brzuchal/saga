<?php declare(strict_types=1);

namespace Brzuchal\Saga\Association;

final class AssociationResolver
{
    public function __construct(
        private string $key,
        private AssociationEvaluator $associationEvaluator,
    ) {
    }

    public function resolve(object $message): ?AssociationValue
    {
        if (!$this->associationEvaluator->supports(\get_class($message), $this->key)) {
            throw new \UnexpectedValueException(\sprintf(
                'Class %s is not supported by given association evaluator',
                \get_class($message),
            ));
        }

        $value = $this->associationEvaluator->evaluate($message);

        return new AssociationValue($this->key, $value);
    }
}
