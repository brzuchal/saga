<?php declare(strict_types=1);

namespace Brzuchal\Saga\Store;

use Brzuchal\Saga\Association\AssociationValue;
use Brzuchal\Saga\Repository\SagaStoreEntry;
use Brzuchal\Saga\SagaState;

/** @internal  */
final class SimpleSagaStoreEntry implements SagaStoreEntry
{
    public function __construct(
        protected object $saga,
        /** @psalm-var AssociationValue */
        protected array $associationValues,
        protected SagaState $state = SagaState::Pending,
    ) {
    }

    public function object(): object
    {
        return $this->saga;
    }

    /** @inheritdoc */
    public function associationValues(): array
    {
        return $this->associationValues;
    }

    public function state(): SagaState
    {
        return $this->state;
    }
}
