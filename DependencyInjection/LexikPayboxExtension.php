<?php

namespace Lexik\Bundle\PayboxBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
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

        $this->processAccountsConfiguration($config, $container);
    }

    private function processAccountsConfiguration(array $config, ContainerBuilder $container)
    {
        foreach ($config['accounts'] as $name => $options) {
            if (null === $options['public_key']) {
                $options['public_key'] = __DIR__ . '/../Resources/config/paybox_public_key.pem';
            }

            $options['parameters']['public_key'] = $options['public_key'];

            $this
                ->createTransportService($name, $options, $container)
                ->createRequestHandlerService($name, $options, $container)
                ->createRequestCancellationHandlerService($name, $options, $container)
                ->createResponseHandlerService($name, $options, $container)
                ->createDirecPlusRequestHandlerService($name, $options, $container)
            ;
        }
    }

    private function createTransportService($name, array $options, ContainerBuilder $container)
    {
        $client = new Definition($options['transport']);

        $clientServiceId = sprintf('lexik_paybox.transport.%s', $name);
        $container->setDefinition($clientServiceId, $client);

        return $this;
    }

    private function createRequestHandlerService($name, array $options, ContainerBuilder $container)
    {
        $client = new Definition($container->getParameterBag()->get('lexik_paybox.request_handler.class'));

        $client->addArgument($name);
        $client->addArgument($options['parameters']);
        $client->addArgument($options['servers']);
        $client->addArgument(new Reference('form.factory'));

        $clientServiceId = sprintf('lexik_paybox.request_handler.%s', $name);
        $container->setDefinition($clientServiceId, $client);

        return $this;
    }

    private function createRequestCancellationHandlerService($name, array $options, ContainerBuilder $container)
    {
        $client = new Definition($container->getParameterBag()->get('lexik_paybox.request_cancellation_handler.class'));

        $client->addArgument($name);
        $client->addArgument($options['parameters']);
        $client->addArgument($options['servers']);
        $client->addArgument(new Reference(sprintf('lexik_paybox.transport.%s', $name)));

        $clientServiceId = sprintf('lexik_paybox.request_cancellation_handler.%s', $name);
        $container->setDefinition($clientServiceId, $client);

        return $this;
    }

    private function createResponseHandlerService($name, array $options, ContainerBuilder $container)
    {
        $client = new Definition($container->getParameterBag()->get('lexik_paybox.response_handler.class'));

        $client->addArgument(new Reference('request_stack'));
        $client->addArgument(new Reference('logger'));
        $client->addArgument(new Reference('event_dispatcher'));
        $client->addArgument($options['parameters']);

        $client->addTag('monolog.logger', ['channel' => 'payment']);

        $clientServiceId = sprintf('lexik_paybox.response_handler.%s', $name);
        $container->setDefinition($clientServiceId, $client);

        return $this;
    }

    private function createDirecPlusRequestHandlerService($name, array $options, ContainerBuilder $container)
    {
        $client = new Definition($container->getParameterBag()->get('lexik_paybox.direc_plus.request_handler.class'));

        $client->addArgument($name);
        $client->addArgument($options['parameters']);
        $client->addArgument($options['servers']);
        $client->addArgument(new Reference('logger'));
        $client->addArgument(new Reference('event_dispatcher'));
        $client->addArgument(new Reference(sprintf('lexik_paybox.transport.%s', $name)));

        $client->addTag('monolog.logger', ['channel' => 'payment']);

        $clientServiceId = sprintf('lexik_paybox.direc_plus.request_handler.%s', $name);
        $container->setDefinition($clientServiceId, $client);

        return $this;
    }
}
