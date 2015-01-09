<?php

namespace Lexik\Bundle\PayboxBundle\Paybox;

/**
 * Class RequestInterface
 *
 * @package Lexik\Bundle\PayboxBundle\Paybox
 *
 * @author Lexik <dev@lexik.fr>
 * @author Olivier Maisonneuve <o.maisonneuve@lexik.fr>
 */
interface RequestInterface
{
    /**
     * Sets a parameter.
     *
     * @param  string $name
     * @param  mixed  $value
     *
     * @return RequestInterface
     */
    public function setParameter($name, $value);

    /**
     * Sets a bunch of parameters.
     *
     * @param  array $parameters
     *
     * @return RequestInterface
     */
    public function setParameters(array $parameters);

    /**
     * Returns a parameter.
     *
     * @param  string $name
     *
     * @return array
     */
    public function getParameter($name);

    /**
     * Returns all parameters set for a payment.
     *
     * @return array
     */
    public function getParameters();

    /**
     * Returns the url of the server.
     *
     * @return string
     */
    public function getUrl();
}
