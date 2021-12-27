<?php declare(strict_types=1);

namespace Brzuchal\Saga;

use Brzuchal\Saga\Association\AssociationValue;
use Brzuchal\Saga\Mapping\IncompleteSagaMetadata;
use Brzuchal\Saga\Mapping\SagaMetadataRepository;
use Brzuchal\Saga\Store\SagaStore;
use Exception;

final class SagaManager
{
    public function __construct(
        protected SagaStore $store,
        protected SagaMetadataRepository $repository,
        protected SagaIdentifierGenerator $identifierGenerator = new SagaIdentifierGenerator(),
    ) {
    }

    /**
     * @throws SagaInstanceNotFound
     * @throws IncompleteSagaMetadata
     */
    public function __invoke(object $message): void
    {
        $sagas = $this->repository->findByMessage($message);
        $nonInvokedSagaTypes = \array_keys($sagas);
        foreach ($sagas as $type => $metadata) {
            $associationValue = $metadata->resolveAssociation($message);
            foreach ($this->store->findSagas($type, $associationValue) as $identifier) {
                // at this point we also need to filter closed instances
                $saga = $this->store->loadSaga($type, $identifier);
                if ($saga === null) {
                    throw SagaInstanceNotFound::unableToLoad($type, $associationValue);
                }

                $this->doInvokeSaga($saga, $message, $associationValue);
                unset($nonInvokedSagaTypes[\array_search($type, $nonInvokedSagaTypes, true)]);
            }
        }
        if (!empty($nonInvokedSagaTypes)) {
            foreach ($nonInvokedSagaTypes as $type) {
                $metadata = $sagas[$type];
                $associationValue = $metadata->resolveAssociation($message);
                // TODO: verify metadata instantiation policy
                $this->startNewSaga($type, $message, $associationValue);
            }
        }
    }

    /**
     * @throws IncompleteSagaMetadata
     */
    protected function doInvokeSaga(object $instance, object $message, AssociationValue $associationValue): void
    {
        $metadata = $this->repository->find(\get_class($instance));
        $method = $metadata->findHandlerMethod($message);
        $lifecycle = new SagaLifecycle(true, [$associationValue]);
        $instance->{$method}($message, $lifecycle);
    }

    /**
     * @param class-string $type
     * @throws IncompleteSagaMetadata
     * @throws IdentifierGenerationFailed
     */
    protected function startNewSaga(string $type, object $message, AssociationValue $associationValue): void
    {
        $metadata = $this->repository->find($type);
        $instance = $metadata->newInstance();

        $lifecycle = new SagaLifecycle(true, [$associationValue]);
        $method = $metadata->findHandlerMethod($message);
        $instance->{$method}($message, $lifecycle);
        $this->store->insertSaga(
            $metadata->getName(),
            $this->identifierGenerator->generateIdentifier(),
            $instance,
            $lifecycle->associationValues(),
        );
    }
}
