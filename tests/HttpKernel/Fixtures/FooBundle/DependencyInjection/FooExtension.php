<?php
declare(strict_types=1);

namespace Zalas\BundleTest\Tests\HttpKernel\Fixtures\FooBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class FooExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        if ($config['enabled']) {
            $loader = new XmlFileLoader($container, new FileLocator(dirname(__DIR__).'/Resources/config'));
            $loader->load('services.xml');
        }
    }
}