<?php

namespace Lexik\Bundle\PayboxBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $paybox = $this->get('lexik_paybox.request_handler');
        $paybox->setParameters(array(
            'PBX_CMD'     => 'CMD'.time(),
            'PBX_DEVISE'  => '978',
            'PBX_PORTEUR' => 'test@test.net',
            'PBX_RETOUR'  => 'Mt:M;Ref:R;Auto:A;Erreur:E',
            'PBX_TOTAL'   => '1000',
        ));

        return $this->render(
            'LexikPayboxBundle:Default:index.html.twig',
            array(
                'url'  => $paybox->getUrl(),
                'form' => $paybox->getSimplePaymentForm()->createView(),
            )
        );
    }
}
