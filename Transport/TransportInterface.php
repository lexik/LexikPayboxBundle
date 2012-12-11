<?php

namespace Lexik\Bundle\PayboxBundle\Transport;

use Lexik\Bundle\PayboxBundle\Paybox\System\CancellationRequest;

/**
 * Transport\TransportInterface class.
 *
 * @author Fabien POMEROL <fabien.pomerol@gmail.com>
 */
interface TransportInterface
{
    /**
     * Prepare and send a message.
     *
     * @param CancellationRequest $request Request instance
     *
     * @return String The Paybox response
     */
    public function call(CancellationRequest $request);
}
