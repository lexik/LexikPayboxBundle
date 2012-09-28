<?php

namespace Lexik\Bundle\PayboxBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    /**
     * IPN action.
     *
     * @return Response
     */
    public function ipnAction()
    {
        $payboxResponse = $this->container->get('lexik_paybox.response_handler');
        $result = $payboxResponse->verifySignature();

        return $this->render('LexikPayboxBundle:Default:ipn.html.twig', array(
            'status' => $result ? 'OK' : 'KO',
        ));
    }
}
