<?php

namespace Lexik\Bundle\PayboxBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DirectPlusController
 *
 * @package Lexik\Bundle\PayboxBundle\Controller
 *
 * @author Romain Marecat <romain.marecat@gmail.com>
 */
class RemoteMpiController extends Controller
{
    public function indexAction()
    {
        $paybox = $this->get('lexik_paybox.remote_mpi.request_handler');
        $paybox->setParameters([
            'Amount'                => sprintf('%010d', '100'),
            'CCExpDate'             => sprintf('%04d', '0117'),
            'CCNumber'              => sprintf('%16d', '1111222233334444'),
            'Currency'              => sprintf('%3d', '978'),
            'CVVCode'               => sprintf('%3d', '123'),
            'IdSession'             => sprintf('%s', 'ORDER' . rand(1000, 9999)),
            'URLHttpDirect'         => $this->generateUrl('lexik_paybox_remote_mpi_return'),
            'URLRetour'             => $this->generateUrl('lexik_paybox_remote_mpi_return'),
        ]);

        return $this->render(
            'LexikPayboxBundle:RemoteMpi:index.html.twig',
            array(
                'url'  => $paybox->getUrl(),
                'form' => $paybox->getForm()->createView(),
            )
        );
    }

    public function returnAction(Request $request)
    {
        return $this->render('LexikPayboxBundle:RemoteMpi:return.html.twig', array(
            'parameters' => $request->request,
        ));
    }
}
