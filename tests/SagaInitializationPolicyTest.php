<?php

namespace Brzuchal\Saga\Tests;

use Brzuchal\Saga\Association\AssociationValue;
use Brzuchal\Saga\SagaCreationPolicy;
use Brzuchal\Saga\SagaInitializationPolicy;
use PHPUnit\Framework\TestCase;

class SagaInitializationPolicyTest extends TestCase
{
    public function testCreateNoneCreationPolicy(): void
    {
        $associationValue = new AssociationValue('id', '7992d785-a07c-481b-bf34-a325847427b0');
        $policy = new SagaInitializationPolicy(SagaCreationPolicy::NONE, $associationValue);
        $this->assertFalse($policy->createAlways());
        $this->assertFalse($policy->createIfNoneFound());
        $this->assertSame($associationValue, $policy->initialAssociationValue());
    }

    public function testCreateIfNoneFoundCreationPolicy(): void
    {
        $associationValue = new AssociationValue('id', '7992d785-a07c-481b-bf34-a325847427b0');
        $policy = new SagaInitializationPolicy(SagaCreationPolicy::IF_NONE_FOUND, $associationValue);
        $this->assertFalse($policy->createAlways());
        $this->assertTrue($policy->createIfNoneFound());
        $this->assertSame($associationValue, $policy->initialAssociationValue());
    }

    public function testCreateAlwaysCreationPolicy(): void
    {
        $associationValue = new AssociationValue('id', '7992d785-a07c-481b-bf34-a325847427b0');
        $policy = new SagaInitializationPolicy(SagaCreationPolicy::ALWAYS, $associationValue);
        $this->assertTrue($policy->createAlways());
        $this->assertFalse($policy->createIfNoneFound());
        $this->assertSame($associationValue, $policy->initialAssociationValue());
    }
}
