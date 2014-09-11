<?php

namespace Lexik\Bundle\PayboxBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class LexikPayboxExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $container->setParameter('lexik_paybox.servers', $config['servers']);
        $container->setParameter('lexik_paybox.parameters', $config['parameters']);
        $container->setParameter('lexik_paybox.transport.class', $config['transport']);

        if (null === $config['parameters']['public_key']) {
            $config['parameters']['public_key'] = __DIR__ . '/../Resources/config/paybox_public_key.pem';
            $container->setParameter('lexik_paybox.parameters', $config['parameters']);
        }

        $container->setParameter('lexik_paybox.public_key', $config['parameters']['public_key']);
    }
}
