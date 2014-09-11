<?php

namespace Lexik\Bundle\PayboxBundle\Paybox\System\Base;

use Symfony\Component\Form\FormFactoryInterface;

use Lexik\Bundle\PayboxBundle\Paybox\Paybox;
use Lexik\Bundle\PayboxBundle\Paybox\System\Base\ParameterResolver;

/**
 * Paybox\System\Request class.
 *
 * @author Olivier Maisonneuve <o.maisonneuve@lexik.fr>
 */
class Request extends Paybox
{
    /**
     * @var FormFactory
     */
    protected $factory;

    /**
     * Constructor.
     *
     * @param array                $parameters
     * @param array                $servers
     * @param FormFactoryInterface $factory
     */
    public function __construct(array $parameters, array $servers, FormFactoryInterface $factory)
    {
        parent::__construct($parameters, $servers);

        $this->factory = $factory;
    }

    /**
     * @see Paybox::initParameters()
     */
    protected function initParameters()
    {
        $this->setParameter('PBX_SITE', $this->globals['site']);
        $this->setParameter('PBX_RANG', $this->globals['rank']);
        $this->setParameter('PBX_IDENTIFIANT', $this->globals['login']);
        $this->setParameter('PBX_HASH', $this->globals['hmac_algorithm']);
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
        $builder = $this->factory->createNamedBuilder('', 'form', $parameters, $options);

        foreach ($parameters as $key => $value) {
            $builder->add($key, 'hidden');
        }

        return $builder->getForm();
    }

    /**
     * Returns the url of an available server.
     *
     * @param  string $env
     *
     * @return string
     */
    public function getUrl($env = 'dev')
    {
        $server = $this->getServer($env);

        return sprintf(
            '%s://%s%s',
            $server['protocol'],
            $server['host'],
            $server['system_path']
        );
    }
}
