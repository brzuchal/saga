<?php declare(strict_types=1);

namespace Brzuchal\Saga\Tests\Mapping;

use Brzuchal\Saga\Mapping\AttributeMappingDriver;
use Brzuchal\Saga\Tests\Fixtures\AttributedFoo;
use Brzuchal\Saga\Tests\Fixtures\FooMessage;
use PHPUnit\Framework\TestCase;

class AttributeSagaMetadataFactoryTest extends TestCase
{
    public function testSingleMethodFactory(): void
    {
        $factory = new AttributeMappingDriver();
        $metadata = $factory->loadMetadataForClass(AttributedFoo::class);
        $this->assertEquals(AttributedFoo::class, $metadata->getType());
        $message = new FooMessage();
        $this->assertEquals(AttributedFoo::class, $metadata->getType());
        $this->assertTrue($metadata->hasHandlerMethod($message));
        $this->assertEquals('foo', $metadata->findHandlerMethod($message));
        $associationValue = $metadata->resolveAssociation($message);
        $this->assertNotNull($associationValue);
        $this->assertEquals('keyInt', $associationValue->getKey());
        $this->assertEquals($message->getId(), $associationValue->getValue());
    }
}
