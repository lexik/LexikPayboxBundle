<?php

namespace Lexik\Bundle\PayboxBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGenerator;

/**
 * Class SampleController
 *
 * @package Lexik\Bundle\PayboxBundle\Controller
 *
 * @author Lexik <dev@lexik.fr>
 * @author Olivier Maisonneuve <o.maisonneuve@lexik.fr>
 */
class SampleController extends Controller
{
    /**
     * Index action creates the form for a payment call.
     *
     * @param string $account
     *
     * @return Response
     */
    public function indexAction($account)
    {
        $service = sprintf('lexik_paybox.request_handler.%s', $account);

        if (!$this->has($service)) {
            throw new NotFoundHttpException(sprintf('Service %s not found', $service));
        }

        $paybox = $this->get($service);
        $paybox->setParameters(array(
            'PBX_CMD'          => 'CMD'.time(),
            'PBX_DEVISE'       => '978',
            'PBX_PORTEUR'      => 'test@paybox.com',
            'PBX_RETOUR'       => 'Mt:M;Ref:R;Auto:A;Erreur:E',
            'PBX_TOTAL'        => '1000',
            'PBX_TYPEPAIEMENT' => 'CARTE',
            'PBX_TYPECARTE'    => 'CB',
            'PBX_EFFECTUE'     => $this->generateUrl('lexik_paybox_sample_return', array('account' => $account, 'status' => 'success'), UrlGenerator::ABSOLUTE_URL),
            'PBX_REFUSE'       => $this->generateUrl('lexik_paybox_sample_return', array('account' => $account, 'status' => 'denied'), UrlGenerator::ABSOLUTE_URL),
            'PBX_ANNULE'       => $this->generateUrl('lexik_paybox_sample_return', array('account' => $account, 'status' => 'canceled'), UrlGenerator::ABSOLUTE_URL),
            'PBX_RUF1'         => 'POST',
            'PBX_REPONDRE_A'   => $this->generateUrl('lexik_paybox_ipn', array('account' => $account, 'time' => time()), UrlGenerator::ABSOLUTE_URL),
        ));

        return $this->render(
            'LexikPayboxBundle:Sample:index.html.twig',
            array(
                'url'  => $paybox->getUrl(),
                'form' => $paybox->getForm()->createView(),
            )
        );
    }

    /**
     * Return action for a confirmation payment page on which the user is sent
     * after he seizes his payment informations on the Paybox's platform.
     * This action might only containts presentation logic.
     *
     * @param Request $request
     * @param string  $status
     * @param string  $account
     *
     * @return Response
     */
    public function returnAction(Request $request, $status, $account)
    {
        return $this->render('LexikPayboxBundle:Sample:return.html.twig', array(
            'status'     => $status,
            'account'    => $account,
            'parameters' => $request->query,
        ));
    }
}
