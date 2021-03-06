<?php

declare(strict_types=1);

namespace Brzuchal\Saga\Repository;

use Brzuchal\Saga\Mapping\SagaMetadataFactory;
use Brzuchal\Saga\SagaIdentifierGenerator;
use Brzuchal\Saga\SagaRepository;
use Brzuchal\Saga\SagaRepositoryFactory;
use Brzuchal\Saga\Store\SagaStore;
use Closure;

final class SimpleSagaRepositoryFactory implements SagaRepositoryFactory
{
    public function __construct(
        protected SagaStore $store,
        protected Closure $factory,
        protected SagaMetadataFactory $metadataFactory,
        protected SagaIdentifierGenerator $identifierGenerator = new SagaIdentifierGenerator(),
    ) {
    }

    /**
     * @param class-string $type
     */
    public function create(string $type): SagaRepository
    {
        return new SimpleSagaRepository(
            $this->store,
            $this->factory,
            $this->metadataFactory->create($type),
            $this->identifierGenerator,
        );
    }
}
