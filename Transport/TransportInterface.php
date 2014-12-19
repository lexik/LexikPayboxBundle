<?php

namespace Lexik\Bundle\PayboxBundle\Transport;

use Lexik\Bundle\PayboxBundle\Paybox\System\Cancellation\Request;

/**
 * Interface TransportInterface
 *
 * @package Lexik\Bundle\PayboxBundle\Transport
 *
 * @author Fabien Pomerol <fabien.pomerol@gmail.com>
 */
interface TransportInterface
{
    /**
     * Prepare and send a message.
     *
     * @param Request $request Request instance
     *
     * @return string The Paybox response
     */
    public function call(Request $request);

    /**
     * Define the endpoint.
     * It can be an url or a path, depends what control you choose.
     *
     * @param string $endpoint to paybox endpoint
     */
    public function setEndpoint($endpoint);
}
