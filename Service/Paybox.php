<?php

namespace Lexik\Bundle\PayboxBundle\Service;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

use Lexik\Bundle\PayboxBundle\Service\PayboxParameterResolver;

class Paybox
{
    /**
     * Array of parameters of the transaction.
     *
     * @var array
     */
    protected $parameters;

    /**
     * Key used to compute the hmac hash.
     *
     * @var string
     */
    protected $hmacKey;

    /**
     * Constructor.
     *
     * @param array $parameters
     */
    public function __construct(array $parameters)
    {
        if (!function_exists('hash_hmac')) {
            throw new InvalidConfigurationException('Function "hash_hmac()" unavailable. You need to install "PECL hash >= 1.1".');
        }

        $this->parameters = array();

        $this->initParameters($parameters);
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

        $this->parameters[$name] = $value;

        return $this;
    }

    /**
     * Sets a bunch of parameters.
     *
     * @param array $parameters
     */
    public function setParameters(array $parameters)
    {
        foreach ($parameters as $name => $value) {
            $this->setParameter($name, $value);
        }

        return $this;
    }

    /**
     * Returns a parameter.
     *
     * @param  string $name
     * @return array
     */
    public function getParameter($name)
    {
        return (isset($this->parameters[strtoupper($name)])) ? $this->parameters[strtoupper($name)] : null;
    }

    /**
     * Returns all parameters set for a payment.
     *
     * @return array
     */
    public function getSimplePaymentParameters()
    {
        if (!isset($this->parameters['PBX_HMAC'])) {
            $this->computeHmac();
        }

        $resolver = new PayboxParameterResolver();

        return $resolver->resolveSimplePaiement($this->parameters);
    }

    /**
     * Initialize the object with the defaults values.
     *
     * @param  array  $parameters
     */
    protected function initParameters(array $parameters)
    {
        $this->hmacKey = $parameters['hmac']['key'];

        $this->setParameter('PBX_SITE', $parameters['site']);
        $this->setParameter('PBX_RANG', $parameters['rank']);
        $this->setParameter('PBX_IDENTIFIANT', $parameters['login']);
        $this->setParameter('PBX_HASH', $parameters['hmac']['algorithm']);
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
     * Returns all parameters as a querystring.
     *
     * @return string
     */
    protected function stringifyParameters()
    {
        if (isset($this->parameters['PBX_HMAC'])) {
            unset($this->parameters['PBX_HMAC']);
        }

        ksort($this->parameters);

        // $querystring = '';
        // foreach ($this->parameters as $key => $value) {
        //     $querystring .= sprintf('%s%s=%s', ($querystring == '') ? '' : '&', $key, $value);
        // }

        // return $querystring;

        return self::stringify($this->parameters);
    }

    /**
     * Computes the hmac hash.
     */
    protected function computeHmac()
    {
        $this->setParameter('PBX_TIME', date('c'));

        $binKey = pack('H*', $this->hmacKey);
        $hmac = hash_hmac($this->getParameter('PBX_HASH'), $this->stringifyParameters(), $binKey);

        $this->setParameter('PBX_HMAC', strtoupper($hmac));
    }

    /**
     * Makes an array of parameters become a querystring like string.
     *
     * @param  array $array
     * @return string
     */
    static public function stringify(array $array)
    {
        $result = array();

        foreach ($array as $key => $value) {
            $result[] = sprintf('%s=%s', $key, $value);
        }

        return implode('&', $result);
    }
}
