<?php declare(strict_types=1);

namespace Brzuchal\SagaBundle\Tests;

use Brzuchal\Saga\SagaRepositoryFactory;
use Brzuchal\Saga\Tests\Fixtures\AttributedFoo;
use Brzuchal\Saga\Tests\Fixtures\FooMessage;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SagaBundleTest extends KernelTestCase
{
    protected static $class = TestKernel::class;
    protected SagaRepositoryFactory $repositoryFactory;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        /** @var ContainerInterface $container */
        $container = $kernel->getContainer()->get('test.service_container');
        \assert($container instanceof ContainerInterface);
        $this->repositoryFactory = $container->get(SagaRepositoryFactory::class);
    }

    public function testCreate(): void
    {
        $repository = $this->repositoryFactory->create(AttributedFoo::class);
        $this->assertTrue($repository->supports(new FooMessage()));
    }
}
