<?php declare(strict_types=1);

namespace Brzuchal\Saga;

use Brzuchal\Saga\Association\AssociationValue;
use Brzuchal\Saga\Exception\SagaInstanceNotFound;
use Exception;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class SagaManager
{
    public function __construct(
        protected SagaStore $store,
        protected SagaMetadataRepository $repository,
        protected SagaIdentifierGenerator $identifierGenerator = new SagaIdentifierGenerator(),
        protected LoggerInterface $logger = new NullLogger(),
    ) {
    }

    public function __invoke(object $message): void
    {
        $sagas = $this->repository->findByMessage($message);
//        $this->logger->info('Finding saga instances handling {message}', ['message' => \get_class($message)]);
        $nonInvokedSagaTypes = \array_keys($sagas);
        foreach ($sagas as $type => $metadata) {
            $associationValue = $metadata->resolveAssociation($message);
            // TODO: investigate if assuming not null value here is not an issue
            \assert($associationValue instanceof AssociationValue);
            foreach ($this->store->findSagas($type, $associationValue) as $identifier) {
//                $this->logger->info('Handling {message} by {saga} identified with {identifier}', [
//                    'message' => \get_class($message),
//                    'saga' => $type,
//                    'identifier' => $identifier,
//                ]);
                // at this point we also need to filter out non active sagas, for eg. closed or dead
                $saga = $this->store->loadSaga($type, $identifier);
                if ($saga === null) {
                    throw new SagaInstanceNotFound($type, $associationValue);
                }
                $this->doInvokeSaga($saga, $message, $associationValue);
                unset($nonInvokedSagaTypes[\array_search($type, $nonInvokedSagaTypes, true)]);
            }
        }
        if (!empty($nonInvokedSagaTypes)) {
            foreach ($nonInvokedSagaTypes as $type) {
                $metadata = $sagas[$type];
                $associationValue = $metadata->resolveAssociation($message);
                // TODO: investigate if assuming not null value here is not an issue
                \assert($associationValue instanceof AssociationValue);
//                $this->logger->info('Starting {saga} with {message}', [
//                    'message' => \get_class($message),
//                    'saga' => $type,
//                ]);
                // TODO: verify metadata instantiation policy
                $this->startNewSaga($type, $message, $associationValue);
            }
        }
    }

    protected function doInvokeSaga(object $saga, object $message, AssociationValue $associationValue): void
    {
        // TODO: reimplement
    }

    /**
     * @param class-string $type
     * @throws Exception
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
