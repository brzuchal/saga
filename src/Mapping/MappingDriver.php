<?php declare(strict_types=1);

namespace Brzuchal\Saga\Mapping;

interface MappingDriver
{
    /**
     * @psalm-param class-string $class
     */
    public function loadMetadataForClass(string $class): SagaMetadata|null;
}
