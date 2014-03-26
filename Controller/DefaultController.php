<?php

namespace Lexik\Bundle\PayboxBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Paybox default controller.
 *
 * @author Olivier Maisonneuve <o.maisonneuve@lexik.fr>
 */
class DefaultController extends Controller
{
    /**
     * Instant Payment Notification action.
     * Here, presentation is anecdotic, the server only look at the http status.
     *
     * @return Response
     */
    public function ipnAction()
    {
        $payboxResponse = $this->container->get('lexik_paybox.response_handler');
        $result = $payboxResponse->verifySignature();

        return new Response($result ? 'OK' : 'KO');
    }
}
