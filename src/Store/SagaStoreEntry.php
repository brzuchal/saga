<?php

declare(strict_types=1);

namespace Brzuchal\Saga\Store;

use Brzuchal\Saga\Association\AssociationValues;
use Brzuchal\Saga\SagaState;

interface SagaStoreEntry
{
    public function object(): object;

    public function associationValues(): AssociationValues;

    public function state(): SagaState;
}
