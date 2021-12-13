<?php declare(strict_types=1);

namespace Brzuchal\Saga;

interface SagaMetadataFactory
{
    /**
     * @psalm-param class-string $class
     */
    public function create(string $class): SagaMetadata;
}
