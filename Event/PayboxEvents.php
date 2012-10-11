<?php

namespace Lexik\Bundle\PayboxBundle\Event;

/**
 * PayboxEvents class.
 *
 * @author Olivier Maisonneuve <o.maisonneuve@lexik.fr>
 */
class PayboxEvents
{
    /**
     * The paybox.ipn_response event is triggered each time an IPN
     *
     * The event listener receives an
     * Lexik\Bundle\PayboxBundle\Event\PayboxResponseEvent instance.
     *
     * @var string
     */
    const PAYBOX_IPN_RESPONSE = 'paybox.ipn_response';
}
