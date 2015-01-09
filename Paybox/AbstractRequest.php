<?php

namespace Lexik\Bundle\PayboxBundle\Paybox;

use Lexik\Bundle\PayboxBundle\Paybox\System\Tools;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

/**
 * Class AbstractRequest
 *
 * @package Lexik\Bundle\PayboxBundle\Paybox
 *
 * @author Lexik <dev@lexik.fr>
 * @author Olivier Maisonneuve <o.maisonneuve@lexik.fr>
 */
abstract class AbstractRequest implements RequestInterface
{
    /**
     * Array of the transaction's parameters.
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
     */
    public function __construct(array $parameters, array $servers)
    {
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
    abstract protected function initGlobals(array $parameters);

    /**
     * Initialize defaults parameters with globals.
     */
    abstract protected function initParameters();

    /**
     * {@inheritdoc}
     */
    public function setParameter($name, $value)
    {
        $this->parameters[$name] = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setParameters(array $parameters)
    {
        foreach ($parameters as $name => $value) {
            $this->setParameter($name, $value);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameter($name)
    {
        return (isset($this->parameters[strtoupper($name)])) ? $this->parameters[strtoupper($name)] : null;
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

        return Tools::stringify($this->parameters);
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
     * @return array
     *
     * @throws InvalidArgumentException If the specified environment is not valid (dev/prod).
     * @throws RuntimeException         If no server is available.
     */
    protected function getServer()
    {
        $servers = array();

        if (isset($this->globals['production']) && (true === $this->globals['production'])) {
            $servers[] = $this->servers['primary'];
            $servers[] = $this->servers['secondary'];
        } else {
            $servers[] = $this->servers['preprod'];
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
