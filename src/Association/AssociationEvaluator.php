<?php declare(strict_types=1);

namespace Brzuchal\Saga\Association;

interface AssociationEvaluator
{
    public function evaluate(object $object): string|int;

    /**
     * @psalm-param class-string $type
     */
    public function supports(string $type, string $key): bool;
}
