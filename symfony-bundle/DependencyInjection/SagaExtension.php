<?php declare(strict_types=1);

namespace Brzuchal\SagaBundle\DependencyInjection;

use Brzuchal\Saga\Mapping\AttributeMappingDriver;
use Brzuchal\Saga\Mapping\SagaMetadataFactory;
use Brzuchal\Saga\SagaRepositoryFactory;
use Brzuchal\Saga\Store\DoctrineSagaStore;
use Brzuchal\Saga\Repository\MappedRepositoryFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Reference;
use function Symfony\Component\DependencyInjection\Loader\Configurator\iterator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

final class SagaExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);
        foreach ($config['stores'] as $name => $store) {
            $repositoryFactoryId = "{$this->getAlias()}.stores.{$name}";
            $store['options'] ??= [];
            ['driver' => $driver, 'options' => $options] = $store;
            $service = match ($driver) {
                'doctrine' => $this->createDoctrineStoreService($options, $container->register($repositoryFactoryId)),
                default => throw new InvalidArgumentException("Unsupported store driver, given: {$driver}"),
            };
            $service->addTag("{$this->getAlias()}.store", ['name' => $name]);
        }

        $locator = [];
        $metadata = [];
        foreach ($config['mappings'] as $class => $mapping) {
            $mapping['type'] ??= 'attribute';
            $locator[$class] = new Reference("{$this->getAlias()}.stores.{$mapping['store']}");
            $metadata[$mapping['type']] = true;
        }

        $metadataDriverTag = "{$this->getAlias()}.metadata_driver";
        foreach ($metadata as $type => $enabled) {
            $metadataDriverId = "{$this->getAlias()}.{$type}_metadata";
            $service = $container->register($metadataDriverId, AttributeMappingDriver::class);
            $service->addTag($metadataDriverTag);
        }

        $metadataFactoryId = "{$this->getAlias()}.metadata_factory";
        $service = $container->register($metadataFactoryId, SagaMetadataFactory::class);
        $service->setArgument(0, tagged_iterator($metadataDriverTag));

        $repositoryFactoryId = "{$this->getAlias()}.repository_factory";
        $service = $container->register($repositoryFactoryId, MappedRepositoryFactory::class);
        $service->setArgument(0, iterator($locator));
        $service->setArgument(1, new Reference($metadataFactoryId));
        $service->setPublic(true);
        $container->setAlias(SagaRepositoryFactory::class, $repositoryFactoryId);
    }

    protected function createDoctrineStoreService(array $options, Definition $service): Definition
    {
        $service->setClass(DoctrineSagaStore::class);
        $options['connection'] ??= 'default';
        $service->setArgument(0, new Reference("doctrine.dbal.{$options['connection']}_connection"));

        return $service;
    }

    public function getAlias(): string
    {
        return 'brzuchal_saga';
    }
}
