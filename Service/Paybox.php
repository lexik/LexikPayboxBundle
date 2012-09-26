<?php

namespace Lexik\Bundle\PayboxBundle\Service;

use Lexik\Bundle\PayboxBundle\Service\PayboxParameter;

class Paybox
{
    /**
     * Array of parameters of the transaction.
     *
     * @var array
     */
    protected $parameters;

    /**
     * Key used to compote the hmac hash.
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
        $this->parameters[strtoupper($name)] = $value;

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
            $this->parameters[strtoupper($name)] = $value;
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

        $resolver = new PayboxParameter();

        return $resolver->resolveSimplePaiement($this->parameters);
    }

    /**
     * Initialise the object with the defaults values.
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

        $querystring = '';
        foreach ($this->parameters as $key => $value) {
            $querystring .= sprintf('%s%s=%s', ($querystring == '') ? '' : '&', $key, $value);
        }

        return $querystring;
    }

    /**
     * Computes the hmac.
     */
    protected function computeHmac()
    {
        $this->setParameter('PBX_TIME', date('c'));

        $hmac = hash_hmac($this->getParameter('PBX_HASH'), $this->stringifyParameters(), $this->hmacKey);

        $this->setParameter('PBX_HMAC', $hmac);
    }
}
