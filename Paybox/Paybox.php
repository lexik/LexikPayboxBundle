<?php

namespace Lexik\Bundle\PayboxBundle\Paybox;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

/**
 * Paybox class.
 *
 * @author Lexik <dev@lexik.fr>
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
     * Array of servers informations.
     *
     * @var array
     */
    protected $servers;

    /**
     * Constructor.
     *
     * @param  array $parameters
     * @param  array $servers
     *
     * @throws InvalidConfigurationException If the hash_hmac() function of PECL hash is not available.
     */
    public function __construct(array $parameters, array $servers)
    {
        if (!function_exists('hash_hmac')) {
            throw new InvalidConfigurationException('Function "hash_hmac()" unavailable. You need to install "PECL hash >= 1.1".');
        }

        $this->parameters = array();
        $this->globals    = array();
        $this->servers    = $servers;

        $this->initGlobals($parameters);
        $this->initParameters();
    }

    /**
     * Initialize the object with the defaults values.
     *
     * @param array $parameters
     */
    protected function initGlobals(array $parameters)
    {
        $this->globals = array(
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
     * Initialize defaults parameters with globals.
     */
    abstract protected function initParameters();

    /**
     * Sets a parameter.
     *
     * @param  string $name
     * @param  mixed  $value
     *
     * @return Paybox
     */
    public function setParameter($name, $value)
    {
        $this->parameters[$name] = $value;

        return $this;
    }

    /**
     * Sets a bunch of parameters.
     *
     * @param  array $parameters
     *
     * @return Paybox
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
     *
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
     *
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
     *
     * @return string
     */
    protected function computeHmac()
    {
        $binKey = pack('H*', $this->globals['hmac_key']);

        return hash_hmac($this->globals['hmac_algorithm'], $this->stringifyParameters(), $binKey);
    }

    /**
     * Returns the url of an available server.
     *
     * @param  string $env
     *
     * @return array
     *
     * @throws InvalidArgumentException If the specified environment is not valid (dev/prod).
     * @throws RuntimeException         If no server is available.
     */
    protected function getServer($env = 'dev')
    {
        if (!in_array($env, array('dev', 'prod'))) {
            throw new InvalidArgumentException('Invalid $env argument value.');
        }

        $servers = array();
        if ('dev' === $env) {
            $servers[] = $this->servers['preprod'];
        } else {
            $servers[] = $this->servers['primary'];
            $servers[] = $this->servers['secondary'];
        }

        foreach ($servers as $server) {
            $doc = new \DOMDocument();
            $doc->loadHTML($this->getWebPage(sprintf(
                '%s://%s%s',
                $server['protocol'],
                $server['host'],
                $server['test_path']
            )));
            $element = $doc->getElementById('server_status');

            if ($element && 'OK' == $element->textContent) {
                return $server;
            }
        }

        throw new RuntimeException('No server available.');
    }

    /**
     * Returns the content of a web resource.
     *
     * @param  string $url
     *
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
