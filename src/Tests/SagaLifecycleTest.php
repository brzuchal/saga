<?php

namespace Brzuchal\Saga\Tests;

use Brzuchal\Saga\Association\AssociationValue;
use Brzuchal\Saga\Association\AssociationValues;
use Brzuchal\Saga\SagaLifecycle;
use Brzuchal\Saga\SagaRejected;
use Brzuchal\Saga\SagaState;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class SagaLifecycleTest extends TestCase
{
    public function testCreate(): void
    {
        $associationValues = new AssociationValues([]);
        $lifecycle = new SagaLifecycle(SagaState::Pending, $associationValues);
        $this->assertSame(SagaState::Pending, $lifecycle->getState());
        $this->assertSame($associationValues, $lifecycle->associationValues);
        $this->assertTrue($lifecycle->isActive());
    }

    public function testComplete(): void
    {
        $associationValues = new AssociationValues([]);
        $lifecycle = new SagaLifecycle(SagaState::Pending, $associationValues);
        $lifecycle->complete();
        $this->assertSame(SagaState::Completed, $lifecycle->getState());
        $this->assertFalse($lifecycle->isActive());
    }

    public function testReject(): void
    {
        $associationValues = new AssociationValues([]);
        $lifecycle = new SagaLifecycle(SagaState::Pending, $associationValues);
        try {
            $lifecycle->reject(new RuntimeException('Test'));
        } catch (SagaRejected $exception) {
            $this->assertInstanceOf(RuntimeException::class, $exception->getPrevious());
        }
        $this->assertSame(SagaState::Rejected, $lifecycle->getState());
        $this->assertFalse($lifecycle->isActive());
    }

    public function testAssociateWith(): void
    {
        $associationValues = new AssociationValues([]);
        $lifecycle = new SagaLifecycle(SagaState::Pending, $associationValues);
        $associationValue = new AssociationValue('id', 'bb176ecc-d791-4698-9328-019ce7c93569');
        $lifecycle->associateWith($associationValue->key, $associationValue->value);
        $this->assertTrue($lifecycle->associationValues->contains($associationValue));
    }

    public function testRemoveAssociationWith(): void
    {
        $associationValue = new AssociationValue('id', 'bb176ecc-d791-4698-9328-019ce7c93569');
        $associationValues = new AssociationValues([$associationValue]);
        $lifecycle = new SagaLifecycle(SagaState::Pending, $associationValues);
        $lifecycle->removeAssociationWith($associationValue->key, $associationValue->value);
        $this->assertFalse($lifecycle->associationValues->contains($associationValue));
    }
}
