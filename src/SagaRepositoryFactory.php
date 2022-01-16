<?php declare(strict_types=1);

namespace Brzuchal\Saga;

use Brzuchal\Saga\Mapping\SagaMetadataFactory;
use Brzuchal\Saga\Repository\SagaStore;
use Brzuchal\Saga\Repository\SimpleSagaRepository;

final class SagaRepositoryFactory
{
    public function __construct(
        protected SagaStore $store,
        protected SagaMetadataFactory $metadataFactory,
        protected SagaIdentifierGenerator $identifierGenerator = new SagaIdentifierGenerator(),
    ) {}

    /**
     * @param class-string $type
     */
    public function create(string $type): SagaRepository
    {
        return new SimpleSagaRepository(
            $this->store,
            $this->metadataFactory->create($type),
            $this->identifierGenerator,
        );
    }
}
