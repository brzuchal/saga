<?php

declare(strict_types=1);

namespace Brzuchal\Saga\Tests;

use Brzuchal\Saga\Association\AssociationResolver;
use Brzuchal\Saga\Association\AssociationValue;
use Brzuchal\Saga\Association\AssociationValues;
use Brzuchal\Saga\Association\PropertyNameEvaluator;
use Brzuchal\Saga\Mapping\SagaMetadata;
use Brzuchal\Saga\Mapping\SagaMethodMetadata;
use Brzuchal\Saga\SagaInstance;
use Brzuchal\Saga\SagaState;
use Brzuchal\Saga\Tests\Fixtures\FooMessage;
use Brzuchal\Saga\Tests\Fixtures\FooSaga;
use PHPUnit\Framework\TestCase;

class SagaInstanceTest extends TestCase
{
    public function testCreateAndDefaultValues(): void
    {
        $identifier = 'd9892261-1844-4a11-8e83-daacdee0c00e';
        $saga = new FooSaga();
        $associationValue = new AssociationValue('id', '3dd0b174-7ac9-46b4-ae09-8e4165bbebac');
        $instance = new SagaInstance(
            $identifier,
            $saga,
            new AssociationValues([$associationValue]),
            new SagaMetadata(FooSaga::class, static fn () => new FooSaga(), []),
        );
        $this->assertEquals($identifier, $instance->id);
        $this->assertSame($saga, $instance->instance);
        $this->assertCount(1, $instance->associationValues);
        $this->assertTrue($instance->associationValues->contains($associationValue));
        $this->assertEquals(FooSaga::class, $instance->getType());
        $this->assertSame(SagaState::Pending, $instance->getState());
    }

    public function testCanHandle(): void
    {
        $saga = new FooSaga();
        $associationValue = new AssociationValue('id', '3dd0b174-7ac9-46b4-ae09-8e4165bbebac');
        $associationResolver = new AssociationResolver('id', new PropertyNameEvaluator('id'));
        $instance = new SagaInstance(
            'd9892261-1844-4a11-8e83-daacdee0c00e',
            $saga,
            new AssociationValues([$associationValue]),
            new SagaMetadata(FooSaga::class, static fn () => new FooSaga(), [
                new SagaMethodMetadata('foo', [FooMessage::class], $associationResolver),
            ]),
        );
        $this->assertTrue($instance->canHandle(new FooMessage($associationValue->value)));
    }

    public function testHandle(): void
    {
        $saga = new FooSaga();
        $associationValue = new AssociationValue('id', '3dd0b174-7ac9-46b4-ae09-8e4165bbebac');
        $associationResolver = new AssociationResolver('id', new PropertyNameEvaluator('id'));
        $instance = new SagaInstance(
            'd9892261-1844-4a11-8e83-daacdee0c00e',
            $saga,
            new AssociationValues([$associationValue]),
            new SagaMetadata(FooSaga::class, static fn () => new FooSaga(), [
                new SagaMethodMetadata('foo', [FooMessage::class], $associationResolver),
            ]),
        );
        $instance->handle(new FooMessage($associationValue->value));
        $this->assertTrue($saga->fooInvoked);
        $this->assertNotEmpty($instance->associationValues->addedAssociations());
    }
}
