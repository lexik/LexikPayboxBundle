<?php

namespace Lexik\Bundle\PayboxBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
     * @param string $account
     *
     * @return Response
     */
    public function ipnAction($account)
    {
        $service = sprintf('lexik_paybox.response_handler.%s', $account);

        if (!$this->container->has($service)) {
            throw new NotFoundHttpException(sprintf('Service %s not found', $service));
        }

        $payboxResponse = $this->container->get($service);
        $result = $payboxResponse->verifySignature();

        return new Response($result ? 'OK' : 'KO');
    }
}
