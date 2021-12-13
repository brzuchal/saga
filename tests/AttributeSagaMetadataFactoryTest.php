<?php declare(strict_types=1);

namespace Brzuchal\Saga\Tests;

use Brzuchal\Saga\AttributeSagaMetadataFactory;
use Brzuchal\Saga\Tests\Fixtures\AttributedFoo;
use Brzuchal\Saga\Tests\Fixtures\FooMessage;
use PHPUnit\Framework\TestCase;

class AttributeSagaMetadataFactoryTest extends TestCase
{
    public function testSingleMethodFactory(): void
    {
        $factory = new AttributeSagaMetadataFactory();
        $metadata = $factory->create(AttributedFoo::class);
        $this->assertEquals(AttributedFoo::class, $metadata->getName());
        $message = new FooMessage();
        $this->assertTrue($metadata->hasHandlerMethod($message));
    }
}
