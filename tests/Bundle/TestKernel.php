<?php declare(strict_types=1);

namespace Brzuchal\Saga\Tests\Bundle;

use Brzuchal\Saga\Bundle\SagaBundle;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;

final class TestKernel extends Kernel implements CompilerPassInterface
{
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        return [
            new DoctrineBundle(),
            new FrameworkBundle(),
            new SagaBundle(),
        ];
    }

    public function getCacheDir(): string
    {
        return \sprintf('%s/var/cache', $this->getProjectDir());
    }

    public function getLogDir(): string
    {
        return \sprintf('%s/var/logs', $this->getProjectDir());
    }

    /**
     * {@inheritdoc}
     */
    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader)
    {
        $container->loadFromExtension('doctrine', [
            'dbal' => [
                'driver' => 'sqlite',
                'charset' => 'utf8mb4',
                'url' => 'sqlite:///:memory:',
                'default_table_options' => [
                    'charset' => 'utf8mb4',
                    'utf8mb4_unicode_ci' => 'utf8mb4_unicode_ci',
                ],
            ],
        ]);

        $container->loadFromExtension('framework', [
            'secret' => 'nope',
            'test' => true,
            'messenger' => null,
            'serializer' => null,
        ]);

        // TODO: add bundle config here
    }

    public function process(ContainerBuilder $container)
    {
        // TODO: Implement process() method.
    }
}
