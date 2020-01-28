<?php

namespace RokkaCli\DependencyInjection;

use Rokka\Client\Base as RokkaBase;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('rokka_cli');
        // Keep compatibility with symfony/config < 4.2
        if (!method_exists($treeBuilder, 'getRootNode')) {
            $rootNode = $treeBuilder->root('rokka_cli');
        } else {
            $rootNode = $treeBuilder->getRootNode();
        }

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
