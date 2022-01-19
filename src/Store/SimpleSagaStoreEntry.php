<?php declare(strict_types=1);

namespace Brzuchal\Saga\Store;

use Brzuchal\Saga\Association\AssociationValues;
use Brzuchal\Saga\SagaState;

/** @internal  */
final class SimpleSagaStoreEntry implements SagaStoreEntry
{
    public function __construct(
        protected readonly object $saga,
        protected readonly AssociationValues $associationValues,
        protected SagaState $state = SagaState::Pending,
    ) {
    }

    public function object(): object
    {
        return $this->saga;
    }

    public function associationValues(): AssociationValues
    {
        return $this->associationValues;
    }

    public function state(): SagaState
    {
        return $this->state;
    }
}
