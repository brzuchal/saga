<?php declare(strict_types=1);

namespace Brzuchal\Saga;

final class SagaManagerFactory
{
    public function __construct(
        protected SagaRepositoryFactory $repositoryFactory,
    ) {
    }

    /**
     * @param class-string $type
     */
    public function getManagerForClass(string $type): SagaManager
    {
        return new SagaManager($this->repositoryFactory->create($type));
    }
}
