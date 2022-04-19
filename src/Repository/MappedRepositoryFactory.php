<?php

declare(strict_types=1);

namespace Brzuchal\Saga\Repository;

use Brzuchal\Saga\Factory\ReflectionClassFactory;
use Brzuchal\Saga\Mapping\IncompleteSagaMetadata;
use Brzuchal\Saga\Mapping\SagaMetadataFactory;
use Brzuchal\Saga\SagaIdentifierGenerator;
use Brzuchal\Saga\SagaRepository;
use Brzuchal\Saga\SagaRepositoryFactory;
use Brzuchal\Saga\Store\SagaStore;
use Closure;
use ReflectionException;

final class MappedRepositoryFactory implements SagaRepositoryFactory
{
    /**
     * @psalm-param iterable<class-string, SagaStore> $stores
     * @psalm-param iterable<class-string, Closure> $factories
     */
    public function __construct(
        protected iterable $stores,
        protected iterable $factories,
        protected SagaMetadataFactory $metadataFactory,
        protected SagaIdentifierGenerator $identifierGenerator = new SagaIdentifierGenerator(),
    ) {
    }

    /**
     * @throws ReflectionException
     * @throws IncompleteSagaMetadata
     */
    public function create(string $type): SagaRepository
    {
        $factory = new ReflectionClassFactory($type);
        foreach ($this->factories as $mappedType => $mappedFactory) {
            if ($type !== $mappedType) {
                continue;
            }

            $factory = $mappedFactory;
        }

        foreach ($this->stores as $mappedType => $store) {
            if ($type !== $mappedType) {
                continue;
            }

            return new SimpleSagaRepository(
                $store,
                $factory(...),
                $this->metadataFactory->create($type),
                $this->identifierGenerator,
            );
        }

        // TODO: replace with dedicated exception type
        throw new \RuntimeException('Missing sture type information');
    }
}
