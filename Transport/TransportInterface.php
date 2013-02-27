<?php

namespace Lexik\Bundle\PayboxBundle\Transport;

use Lexik\Bundle\PayboxBundle\Paybox\System\Cancellation\Request;

/**
 * Transport\TransportInterface class.
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
     * @return String The Paybox response
     */
    public function call(Request $request);
}
