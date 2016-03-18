<?php

namespace RokkaCli\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

class RokkaCliExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('rokka_cli.api_uri', $config['api_uri']);
        $container->setParameter('rokka_cli.api_secret', $config['api_secret']);
        $container->setParameter('rokka_cli.api_key', $config['api_key']);
        $container->setParameter('rokka_cli.organization', $config['organization']);
    }
}
