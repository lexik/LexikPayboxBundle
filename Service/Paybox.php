<?php

namespace Lexik\Bundle\PayboxBundle\Service;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 *
 */
abstract class Paybox
{
    /**
     * Array of parameters of the transaction.
     *
     * @var array
     */
    protected $parameters;

    /**
     * Array of globals parameters.
     *
     * @var array
     */
    protected $globals;

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
        $this->globals = array();

        $this->initGlobals($parameters);
        $this->initParameters();
    }

    /**
     * Initialize the object with the defaults values.
     *
     * @param  array  $parameters
     */
    protected function initGlobals(array $parameters)
    {
        $this->globals = array(
            'site'           => $parameters['site'],
            'rank'           => $parameters['rank'],
            'login'          => $parameters['login'],
            'hmac_key'       => $parameters['hmac']['key'],
            'hmac_algorithm' => $parameters['hmac']['algorithm'],
        );
    }

    /**
     * Initialise defaults parameters with globals.
     */
    abstract protected function initParameters();

    /**
     * Sets a parameter.
     *
     * @param string $name
     * @param mixed  $value
     * @return $this
     */
    public function setParameter($name, $value)
    {
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
    abstract public function getParameters();

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

        return self::stringify($this->parameters);
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

    /**
     * Computes the hmac hash.
     */
    protected function computeHmac()
    {
        $binKey = pack('H*', $this->globals['hmac_key']);
        return hash_hmac($this->globals['hmac_algorithm'], $this->stringifyParameters(), $binKey);
    }
}
