<?php declare(strict_types=1);

namespace Brzuchal\Saga\Association;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

final class ExpressionLanguageEvaluator implements AssociationEvaluator
{
    /**
     * @param mixed[] $params
     */
    public function __construct(
        protected string $expression,
        protected string $parameterName,
        /** @var mixed[] */
        protected array $params = []
    ) {
    }

    public function evaluate(object $object): string | int
    {
        static $expression;
        $expression ??= new ExpressionLanguage();

        return $expression->evaluate(
            $this->expression,
            $this->params + [$this->parameterName => $object]
        );
    }

    public function supports(string $type, string $key): bool
    {
        return \class_exists($type);
    }
}
