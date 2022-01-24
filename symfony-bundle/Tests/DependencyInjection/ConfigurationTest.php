<?php declare(strict_types=1);

namespace Brzuchal\SagaBundle\Tests\DependencyInjection;

use Brzuchal\Saga\Tests\Fixtures\Foo;
use Brzuchal\SagaBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

// phpcs:disable
class ConfigurationTest extends TestCase
{
    protected const CONFIG_ROOT = 'brzuchal_saga';

    protected static function processConfig(array $configs): array
    {
        return (new Processor())->processConfiguration(new Configuration(), $configs);
    }

    public function testStoreDefaults(): void
    {
        $config = self::processConfig([
            self::CONFIG_ROOT => [
                'driver' => 'doctrine',
                'options' => [
                    'connection' => 'default_connection',
                ],
                'mappings' => [
                    Foo::class => null,
                ],
            ],
        ]);
        $this->assertArrayHasKey('stores', $config);
        $this->assertIsArray($config['stores']);
        $this->assertArrayHasKey('default', $config['stores']);
        $this->assertIsArray($config['stores']['default']);
        $this->assertEquals([
            'default' => [
                'driver' => 'doctrine',
                'options' => [
                    'connection' => 'default_connection',
                ],
            ],
        ], $config['stores']);
    }

    public function testStoreDetails(): void
    {
        $config = self::processConfig([
            self::CONFIG_ROOT => [
                'stores' =>  [
                    'default' => [
                        'driver' => 'doctrine',
                        'options' => [
                            'connection' => 'default_connection',
                        ],
                    ],
                ],
                'mappings' => [
                    Foo::class => null,
                ],
            ],
        ]);
        $this->assertEquals([
            'default' => [
                'driver' => 'doctrine',
                'options' => [
                    'connection' => 'default_connection',
                ],
            ],
        ], $config['stores']);
    }

    public function testMappingDefaults(): void
    {
        $config = self::processConfig([
            self::CONFIG_ROOT => [
                'driver' => 'doctrine',
                'mappings' => [
                    Foo::class => null,
                ],
            ],
        ]);
        $this->assertArrayHasKey('mappings', $config);
        $this->assertIsArray($config['mappings']);
        $this->assertArrayHasKey(Foo::class, $config['mappings']);
        $this->assertIsArray($config['mappings'][Foo::class]);
        $this->assertEquals([
            Foo::class => [
                'type' => 'attribute',
                'store' => 'default',
                'options' => [],
            ],
        ], $config['mappings']);
    }
}
