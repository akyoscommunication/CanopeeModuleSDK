<?php

namespace Akyos\CanopeeModuleSDK\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * @inheritDoc
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('canopee_module_sdk');

        $treeBuilder
            ->getRootNode()
                ->children()
                    ->scalarNode('user_identifier')->defaultValue('uuid')->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}