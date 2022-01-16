<?php declare(strict_types=1);

namespace Brzuchal\Saga\Repository;

use Brzuchal\Saga\Association\AssociationValue;
use Brzuchal\Saga\SagaState;

interface SagaStoreEntry
{
    public function object(): object;

    /**
     * @psalm-return list<AssociationValue>
     */
    public function associationValues(): array;

    public function state(): SagaState;
}
