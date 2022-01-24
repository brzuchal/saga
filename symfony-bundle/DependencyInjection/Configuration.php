<?php declare(strict_types=1);

namespace Brzuchal\SagaBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('brzuchal_saga');

        /** @var ArrayNodeDefinition */
        $root = $treeBuilder->getRootNode();
        $this->addStoresSection($root);
        $this->addMappingsSection($root);

        return $treeBuilder;
    }

    protected function addStoresSection(ArrayNodeDefinition $root): void
    {
        $root
            ->beforeNormalization()
            ->ifTrue(static function ($v) {
                return is_array($v) && ! array_key_exists('stores', $v) && ! array_key_exists('default_store', $v);
            })
            ->then(static function ($v) {
                // Key that should not be rewritten to the connection config
                $excludedKeys = ['default_store' => true, 'mappings' => true];
                $store = [];
                foreach ($v as $key => $value) {
                    if (isset($excludedKeys[$key])) {
                        continue;
                    }

                    $store[$key] = $v[$key];
                    unset($v[$key]);
                }

                $v['default_store'] = isset($v['default_store']) ? (string) $v['default_store'] : 'default';
                $v['stores'] = [$v['default_store'] => $store];

                return $v;
            })
            ->end()
            ->fixXmlConfig('store')
            ->children()
                ->scalarNode('default_store')->defaultValue('default')->end()
                ->arrayNode('stores')
                    ->fixXmlConfig('option')
                    ->requiresAtLeastOneElement()
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('driver')->defaultValue('doctrine')->end()
                            ->arrayNode('options')
                                ->useAttributeAsKey('key')
                                ->prototype('variable')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    protected function addMappingsSection(ArrayNodeDefinition $root): void
    {
        $root
            ->fixXmlConfig('mapping')
            ->children()
                ->arrayNode('mappings')
                    ->fixXmlConfig('option')
                    ->requiresAtLeastOneElement()
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('type')->defaultValue('attribute')->end()
                            ->scalarNode('store')->defaultValue('default')->end()
                            ->arrayNode('options')
                                ->useAttributeAsKey('key')
                                ->prototype('variable')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }
}
