<?php declare(strict_types=1);

namespace Brzuchal\Saga\Tests\Association;

use Brzuchal\Saga\Association\PropertyNameEvaluator;
use Brzuchal\Saga\Tests\Fixtures\FooMessage;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class PropertyNameEvaluatorTest extends TestCase
{
    public function testEvaluation(): void
    {
        $evaluator = new PropertyNameEvaluator('id');
        $this->assertTrue($evaluator->supports(FooMessage::class, 'id'));
        $message = new FooMessage();
        $this->assertEquals($message->getId(), $evaluator->evaluate($message));
    }

    public function testNotSupported(): void
    {
        $evaluator = new PropertyNameEvaluator('nonExistent');
        $this->assertFalse($evaluator->supports(FooMessage::class, 'nonExistent'));
    }

    public function testFailOnNonExistentMethod(): void
    {
        $evaluator = new PropertyNameEvaluator('nonexistent');
        $this->expectException(RuntimeException::class);
        $evaluator->evaluate(new FooMessage());
    }
}
