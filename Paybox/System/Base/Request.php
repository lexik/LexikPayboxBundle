<?php

namespace Lexik\Bundle\PayboxBundle\Paybox\System\Base;

use Lexik\Bundle\PayboxBundle\Paybox\AbstractRequest;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Class Request
 *
 * @package Lexik\Bundle\PayboxBundle\Paybox\System\Base
 *
 * @author Lexik <dev@lexik.fr>
 * @author Olivier Maisonneuve <o.maisonneuve@lexik.fr>
 */
class Request extends AbstractRequest
{
    /**
     * @var FormFactoryInterface
     */
    protected $factory;

    /**
     * Constructor.
     *
     * @param array                $parameters
     * @param array                $servers
     * @param FormFactoryInterface $factory
     *
     * @throws InvalidConfigurationException If the hash_hmac() function of PECL hash is not available.
     */
    public function __construct(array $parameters, array $servers, FormFactoryInterface $factory)
    {
        if (!function_exists('hash_hmac')) {
            throw new InvalidConfigurationException('Function "hash_hmac()" unavailable. You need to install "PECL hash >= 1.1".');
        }

        parent::__construct($parameters, $servers['system']);

        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    protected function initGlobals(array $parameters)
    {
        $this->globals = array(
            'production'          => isset($parameters['production']) ? $parameters['production'] : false,
            'currencies'          => $parameters['currencies'],
            'site'                => $parameters['site'],
            'rank'                => $parameters['rank'],
            'login'               => $parameters['login'],
            'hmac_key'            => $parameters['hmac']['key'],
            'hmac_algorithm'      => $parameters['hmac']['algorithm'],
            'hmac_signature_name' => $parameters['hmac']['signature_name'],
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function initParameters()
    {
        $this->setParameter('PBX_SITE',        $this->globals['site']);
        $this->setParameter('PBX_RANG',        $this->globals['rank']);
        $this->setParameter('PBX_IDENTIFIANT', $this->globals['login']);
        $this->setParameter('PBX_HASH',        $this->globals['hmac_algorithm']);
    }

    /**
     * Sets a parameter.
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return Request
     */
    public function setParameter($name, $value)
    {
        /**
         * PBX_RETOUR have to be ended by ";Sign:K"
         */
        if ('PBX_RETOUR' == $name = strtoupper($name)) {
            $value = $this->verifyReturnParameter($value);
        }

        return parent::setParameter($name, $value);
    }

    /**
     * Parameter PBX_RETOUR must contain the string ";Sign:K" at the end for ipn signature verification.
     *
     * @param  string $value
     *
     * @return string
     */
    protected function verifyReturnParameter($value)
    {
        if (false !== preg_match('`[^\:]+\:k`i', $value)) {
            $vars = explode(';', $value);

            array_walk($vars, function ($value, $key) use (&$vars) {
                if (false !== stripos($value, ':K')) {
                    unset($vars[$key]);
                }
            });

            $value = implode(';', $vars);
        }

        return sprintf(
            '%s;%s:K',
            $value,
            $this->globals['hmac_signature_name']
        );
    }

    /**
     * Returns all parameters set for a payment.
     *
     * @return array
     */
    public function getParameters()
    {
        if (null === $this->getParameter('PBX_HMAC')) {
            $this->setParameter('PBX_TIME', date('c'));
            $this->setParameter('PBX_HMAC', strtoupper($this->computeHmac()));
        }

        $resolver = new ParameterResolver($this->globals['currencies']);

        return $resolver->resolve($this->parameters);
    }

    /**
     * Returns a form with defined parameters.
     *
     * @param  array $options
     *
     * @return Form
     */
    public function getForm($options = array())
    {
        $options['csrf_protection'] = false;

        $parameters = $this->getParameters();
        // If symfony version is >=2.8 then we use the FQCN for form types
        // Else we use the IDs.
        if (method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix')) {
            $builder = $this->factory->createNamedBuilder(
                '',
                'Symfony\Component\Form\Extension\Core\Type\FormType',
                $parameters,
                $options
            );
            foreach ($parameters as $key => $value) {
                $builder->add($key, 'Symfony\Component\Form\Extension\Core\Type\HiddenType');
            }
        } else {
            $builder = $this->factory->createNamedBuilder('', 'form', $parameters, $options);
            foreach ($parameters as $key => $value) {
                $builder->add($key, 'hidden');
            }
        }

        return $builder->getForm();
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl()
    {
        $server = $this->getServer();

        return sprintf(
            '%s://%s%s',
            $server['protocol'],
            $server['host'],
            $server['system_path']
        );
    }
}
