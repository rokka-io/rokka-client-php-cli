<?php

namespace RokkaCli\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Rokka\Client\Base as RokkaBase;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('rokka_cli');
        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('api_uri')
                    ->defaultValue(RokkaBase::DEFAULT_API_BASE_URL)
                ->end()
                ->scalarNode('api_key')
                    ->defaultValue(null)
                ->end()
                ->scalarNode('api_secret')
                    ->defaultValue(null)
                ->end()
                ->scalarNode('organization')
                    ->defaultValue(null)
                ->end()
        ;

        return $treeBuilder;
    }
}
