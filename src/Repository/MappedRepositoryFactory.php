<?php

declare(strict_types=1);

namespace Brzuchal\Saga\Repository;

use Brzuchal\Saga\Mapping\SagaMetadataFactory;
use Brzuchal\Saga\SagaIdentifierGenerator;
use Brzuchal\Saga\SagaRepository;
use Brzuchal\Saga\SagaRepositoryFactory;
use Brzuchal\Saga\Store\SagaStore;

final class MappedRepositoryFactory implements SagaRepositoryFactory
{
    /** @psalm-param iterable<class-string, SagaStore> $stores*/
    public function __construct(
        /** @psalm-var iterable<class-string, SagaStore> */
        protected iterable $stores,
        protected SagaMetadataFactory $metadataFactory,
        protected SagaIdentifierGenerator $identifierGenerator = new SagaIdentifierGenerator(),
    ) {
    }

    public function create(string $type): SagaRepository
    {
        foreach ($this->stores as $mappedType => $store) {
            if ($type !== $mappedType) {
                continue;
            }

            return new SimpleSagaRepository(
                $store,
                $this->metadataFactory->create($type),
                $this->identifierGenerator,
            );
        }

        // TODO: replace with dedicated exception type
        throw new \RuntimeException('Missing sture type information');
    }
}
