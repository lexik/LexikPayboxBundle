<?php

namespace Lexik\Bundle\PayboxBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DefaultController
 *
 * @package Lexik\Bundle\PayboxBundle\Controller
 *
 * @author Lexik <dev@lexik.fr>
 * @author Olivier Maisonneuve <o.maisonneuve@lexik.fr>
 */
class DefaultController extends Controller
{
    /**
     * Instant Payment Notification action.
     * Here, presentation is anecdotal, the requesting server only looks at the http status.
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
