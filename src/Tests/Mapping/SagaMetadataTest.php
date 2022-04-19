<?php

declare(strict_types=1);

namespace Brzuchal\Saga\Tests\Mapping;

use Brzuchal\Saga\Association\AssociationResolver;
use Brzuchal\Saga\Association\MethodNameEvaluator;
use Brzuchal\Saga\Association\PropertyNameEvaluator;
use Brzuchal\Saga\Factory\ReflectionClassFactory;
use Brzuchal\Saga\Mapping\SagaMetadata;
use Brzuchal\Saga\Mapping\SagaMethodMetadata;
use Brzuchal\Saga\Tests\Fixtures\FooBarMessage;
use Brzuchal\Saga\Tests\Fixtures\FooMessage;
use Brzuchal\Saga\Tests\Fixtures\FooSaga;
use PHPUnit\Framework\TestCase;

class SagaMetadataTest extends TestCase
{
    public function testSingleMethodAssociationValueEvaluation(): void
    {
        $methodMetadata = new SagaMethodMetadata(
            'foo',
            [FooMessage::class],
            new AssociationResolver('fooId', new PropertyNameEvaluator('id')),
        );
        $metadata = new SagaMetadata(
            FooSaga::class,
            [$methodMetadata],
        );
        $message = new FooMessage();
        $associationValue = $metadata->resolveAssociation($message);
        $this->assertEquals('fooId', $associationValue->key);
        $this->assertEquals($message->getId(), $associationValue->value);
    }

    public function testMultiMethodAssociationValueEvaluation(): void
    {
        $foo = new SagaMethodMetadata(
            'foo',
            [FooMessage::class],
            new AssociationResolver('fooId', new PropertyNameEvaluator('id')),
        );
        $fooBar = new SagaMethodMetadata(
            'fooBar',
            [FooBarMessage::class],
            new AssociationResolver('fooId', new MethodNameEvaluator('getId')),
        );
        $metadata = new SagaMetadata(
            FooSaga::class,
            [$foo, $fooBar],
        );
        $message = new FooMessage();
        $associationValue = $metadata->resolveAssociation($message);
        $this->assertEquals('fooId', $associationValue->key);
        $this->assertEquals($message->getId(), $associationValue->value);
    }
}
