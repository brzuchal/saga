<?php

namespace Brzuchal\Saga\Tests\Mapping;

use Brzuchal\Saga\Association\AssociationResolver;
use Brzuchal\Saga\Association\MethodNameEvaluator;
use Brzuchal\Saga\Association\PropertyNameEvaluator;
use Brzuchal\Saga\Factory\ReflectionClassFactory;
use Brzuchal\Saga\Mapping\SagaMetadata;
use Brzuchal\Saga\Mapping\SagaMethodMetadata;
use Brzuchal\Saga\Tests\Fixtures\Foo;
use Brzuchal\Saga\Tests\Fixtures\FooBarMessage;
use Brzuchal\Saga\Tests\Fixtures\FooMessage;
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
            Foo::class,
            \Closure::fromCallable(new ReflectionClassFactory(Foo::class)),
            [$methodMetadata],
        );
        $message = new FooMessage();
        $associationValue = $metadata->resolveAssociation($message);
        $this->assertEquals($message->getId(), $associationValue->getValue());
        $this->assertEquals('fooId', $associationValue->getKey());
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
            Foo::class,
            \Closure::fromCallable(new ReflectionClassFactory(Foo::class)),
            [$foo, $fooBar],
        );
        $message = new FooMessage();
        $associationValue = $metadata->resolveAssociation($message);
        $this->assertEquals($message->getId(), $associationValue->getValue());
        $this->assertEquals('fooId', $associationValue->getKey());
    }
}
