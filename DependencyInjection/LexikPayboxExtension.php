<?php

namespace Lexik\Bundle\PayboxBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Lexik\Bundle\PayboxBundle\Paybox\Paybox;

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
        }

        if('pbx_retour' == $config['parameters']['validation_by']){
            if(!isset($config['parameters']['pbx_retour']) || !$config['parameters']['pbx_retour']){
                throw new \InvalidArgumentException(
                    'The "pbx_retour" option must be set for validation_by "pbx_retour"'
                );
            }else{
                // if PXB_REPONDRE_A is used the signature only sign parameter from PBX_RETOUR without 'Sign' parameter
                $param_signed = explode(';', $config['parameters']['pbx_retour']);
                $param_signed = array_map(function($param){
                    return substr($param, 0, strpos($param, ':'));
                }, $param_signed);
                $param_signed = array_diff($param_signed, array(Paybox::SIGNATURE_PARAMETER));

                $container->setParameter('lexik_paybox.pbx_retour', $param_signed);
            }
        }else{
            $container->setParameter('lexik_paybox.pbx_retour', null);
        }

        $container->setParameter('lexik_paybox.public_key', $config['parameters']['public_key']);
        $container->setParameter('lexik_paybox.validation_by', $config['parameters']['validation_by']);


    }
}
