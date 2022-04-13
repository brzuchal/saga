<?php

declare(strict_types=1);

namespace Brzuchal\Saga\Tests\Association;

use Brzuchal\Saga\Association\MethodNameEvaluator;
use Brzuchal\Saga\Tests\Fixtures\FooMessage;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class MethodNameEvaluatorTest extends TestCase
{
    public function testEvaluation(): void
    {
        $evaluator = new MethodNameEvaluator('getId');
        $this->assertTrue($evaluator->supports(FooMessage::class, 'id'));
        $message = new FooMessage();
        $this->assertEquals($message->getId(), $evaluator->evaluate($message));
    }

    public function testNotSupported(): void
    {
        $evaluator = new MethodNameEvaluator('getNonexistentMethod');
        $this->assertFalse($evaluator->supports(FooMessage::class, 'id'));
    }

    public function testFailOnNonExistentMethod(): void
    {
        $evaluator = new MethodNameEvaluator('getNonexistentMethod');
        $this->expectException(RuntimeException::class);
        $evaluator->evaluate(new FooMessage());
    }
}
