<?php
declare(strict_types=1);

namespace Zalas\BundleTest\Tests\PHPUnit\Fixtures\FooBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('zalas_foo');

        // canBeEnabled() is broken in older Symfony versions
        $rootNode
            ->addDefaultsIfNotSet()
            ->treatFalseLike(array('enabled' => false))
            ->treatTrueLike(array('enabled' => true))
            ->treatNullLike(array('enabled' => true))
            ->beforeNormalization()
                ->ifArray()
                ->then(function ($v) {
                    $v['enabled'] = isset($v['enabled']) ? $v['enabled'] : !empty($v);

                    return $v;
                })
                ->end()
            ->children()
                ->booleanNode('enabled')
                ->defaultFalse();

        return $treeBuilder;
    }
}