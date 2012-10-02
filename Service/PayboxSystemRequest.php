<?php

namespace Lexik\Bundle\PayboxBundle\Service;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

use Lexik\Bundle\PayboxBundle\Service\Paybox;
use Lexik\Bundle\PayboxBundle\Service\PayboxSystemParameterResolver;

/**
 *
 */
class PayboxSystemRequest extends Paybox
{
    /**
     * FormFactory.
     *
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
     * @return $this
     */
    public function setParameter($name, $value)
    {
        /**
         * @todo Hardcoded verification... must find a beter solution,
         *       but the PBX_RETOUR realy must be ended by ";Sign:K"
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

        return $value .= ';Sign:K';
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
            $this->setParameter('PBX_HMAC', strtoupper(parent::computeHmac()));
        }

        $resolver = new PayboxSystemParameterResolver();
        return $resolver->resolve($this->parameters);
    }

    /**
     * Returns a form with defined parameters.
     *
     * @param  array $options
     * @return Form
     */
    public function getForm($options = array())
    {
        $options['csrf_protection'] = false;

        $parameters = $this->getParameters();
        $builder = $this->factory->createBuilder('form', $parameters, $options);

        foreach ($parameters as $key => $value) {
            $builder->add($key, 'hidden');
        }

        return $builder->getForm();
    }

    /**
     * Returns the url of an available server.
     *
     * @param  string $env
     * @return string
     *
     * @throws InvalidArgumentException If the specified environment is not valid (dev/prod).
     * @throws RuntimeException         If no server is available.
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

    /**
     * Returns the content of a web resource.
     *
     * @param  string $url
     * @return string
     */
    protected function getWebPage($url)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL,            $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER,         false);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        $output = curl_exec($curl);
        curl_close($curl);

        return (string) $output;
    }
}
