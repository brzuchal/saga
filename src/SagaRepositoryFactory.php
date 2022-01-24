<?php declare(strict_types=1);

namespace Brzuchal\Saga;

interface SagaRepositoryFactory
{
    /**
     * @param class-string $type
     */
    public function create(string $type): SagaRepository;
}
