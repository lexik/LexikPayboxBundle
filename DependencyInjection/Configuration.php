<?php

namespace Lexik\Bundle\PayboxBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('lexik_paybox');

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('public_key')->defaultValue(null)->end()

                ->arrayNode('parameters')
                    ->isRequired()
                    ->children()
                        ->scalarNode('production')->defaultValue(false)->end()
                        ->arrayNode('currencies')
                            ->defaultValue(array(
                                '036', // AUD
                                '124', // CAD
                                '756', // CHF
                                '826', // GBP
                                '840', // USD
                                '978', // EUR
                            ))
                            ->prototype('scalar')->end()
                        ->end()
                        ->scalarNode('site')->isRequired()->end()
                        ->scalarNode('rank')->defaultValue(null)->end()
                        ->scalarNode('rang')->defaultValue(null)->end()
                        ->scalarNode('login')->defaultValue(null)->end()
                        ->scalarNode('cle')->defaultValue(null)->end()
                        ->arrayNode('hmac')
                            ->isRequired()
                            ->children()
                                ->scalarNode('algorithm')->defaultValue('sha512')->end()
                                ->scalarNode('key')->isRequired()->end()
                                ->scalarNode('signature_name')->defaultValue('Sign')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('servers')
                    ->addDefaultsIfNotSet()
                    ->children()

                        ->arrayNode('system')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->arrayNode('primary')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('protocol')->defaultValue('https')->end()
                                        ->scalarNode('host')->defaultValue('tpeweb.paybox.com')->end()
                                        ->scalarNode('system_path')->defaultValue('/cgi/MYchoix_pagepaiement.cgi')->end()
                                        ->scalarNode('cancellation_path')->defaultValue('/cgi-bin/ResAbon.cgi')->end()
                                        ->scalarNode('test_path')->defaultValue('/load.html')->end()
                                    ->end()
                                ->end()
                                ->arrayNode('secondary')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('protocol')->defaultValue('https')->end()
                                        ->scalarNode('host')->defaultValue('tpeweb1.paybox.com')->end()
                                        ->scalarNode('system_path')->defaultValue('/cgi/MYchoix_pagepaiement.cgi')->end()
                                        ->scalarNode('cancellation_path')->defaultValue('/cgi-bin/ResAbon.cgi')->end()
                                        ->scalarNode('test_path')->defaultValue('/load.html')->end()
                                    ->end()
                                ->end()
                                ->arrayNode('preprod')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('protocol')->defaultValue('https')->end()
                                        ->scalarNode('host')->defaultValue('preprod-tpeweb.paybox.com')->end()
                                        ->scalarNode('system_path')->defaultValue('/cgi/MYchoix_pagepaiement.cgi')->end()
                                        ->scalarNode('cancellation_path')->defaultValue('/cgi-bin/ResAbon.cgi')->end()
                                        ->scalarNode('test_path')->defaultValue('/load.html')->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()

                        ->arrayNode('direct_plus')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->arrayNode('primary')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('protocol')->defaultValue('https')->end()
                                        ->scalarNode('host')->defaultValue('ppps.paybox.com')->end()
                                        ->scalarNode('api_path')->defaultValue('/PPPS.php')->end()
                                    ->end()
                                ->end()
                                ->arrayNode('secondary')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('protocol')->defaultValue('https')->end()
                                        ->scalarNode('host')->defaultValue('ppps1.paybox.com')->end()
                                        ->scalarNode('api_path')->defaultValue('/PPPS.php')->end()
                                    ->end()
                                ->end()
                                ->arrayNode('preprod')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('protocol')->defaultValue('https')->end()
                                        ->scalarNode('host')->defaultValue('preprod-ppps.paybox.com')->end()
                                        ->scalarNode('api_path')->defaultValue('/PPPS.php')->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()

                    ->end()
                ->end()

                ->scalarNode('transport')
                    ->defaultValue('Lexik\Bundle\PayboxBundle\Transport\CurlTransport')
                    ->validate()
                    ->ifTrue(function($v) { return !class_exists($v); })
                        ->thenInvalid('Invalid "transport" parameter.')
                    ->end()
                ->end()

            ->end()
        ;

        return $treeBuilder;
    }
}
