<?php

namespace Lexik\Bundle\PayboxBundle\Transport;

/**
 * Class AbstractTransport
 *
 * @package Lexik\Bundle\PayboxBundle\Transport
 *
 * @author Fabien Pomerol <fabien.pomerol@gmail.com>
 */
abstract class AbstractTransport implements TransportInterface
{
    /**
     * This is what the transport will point. Can be an url or a path (depends
     * what transport you use, cURL or Shell)
     *
     * @var string $endpoint Url or Path
     */
    protected $endpoint;

    /**
     * Construct the object
     *
     * @param string $endpoint to paybox endpoint
     */
    public function __construct($endpoint = '')
    {
        $this->endpoint = $endpoint;
    }

    /**
     * Define the endpoint. It can be an url or a path, depends what control you
     * choose.
     *
     * @param string $endpoint to paybox endpoint
     */
    public function setEndpoint($endpoint)
    {
        if (!is_string($endpoint)) {
            throw new \InvalidArgumentException('$endpoint must be a string.');
        }

        $this->endpoint = $endpoint;
    }

    /**
     * Get the paybox endpoint.
     *
     * @return string
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * Perform a call
     *
     * @throws \RuntimeException If no endpoint defined
     */
    protected function checkEndpoint()
    {
        if ($this->endpoint == '' || null === $this->endpoint || empty($this->endpoint)) {
            throw new \RunTimeException('No endpoint defined.');
        }
    }
}
