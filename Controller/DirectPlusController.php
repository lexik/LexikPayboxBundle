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
class DirectPlusController extends Controller
{
    /**
     * Euro
     */
    const PBX_DEVISE_EURO = '978';

    /**
     * Different version of Paybox direct
     */
    const VERSION_DIRECT            = '00103';
    const VERSION_DIRECT_PLUS       = '00104';
    /**
     * ECL(Electronic Commerce Indicator).
     * Type of ordering items. Need for some banks.
     * 024 - request by internet
     */
    const PBX_ACTIVITE_VALUE = '024';

    /**
     * All payment direct action listed
     */
    const PBX_PAYMENT_ACTION_AUTHORIZE = '00001';
    const PBX_PAYMENT_ACTION_DEBIT = '00002';
    const PBX_PAYMENT_ACTION_AUTHORIZE_CAPTURE = '00003';
    const PBX_PAYMENT_ACTION_CREDIT = '00004';
    const PBX_PAYMENT_ACTION_CANCELLATION = '00005';
    const PBX_PAYMENT_ACTION_CHECK_EXIST_TRANSACTION = '00011';
    const PBX_PAYMENT_ACTION_TRANSACTION_WHITOUT_AUTHORIZE = '00012';
    const PBX_PAYMENT_ACTION_UPDATE_AMOUNT_TRANSACTION = '00013';
    const PBX_PAYMENT_ACTION_REFUND = '00014';
    const PBX_PAYMENT_ACTION_READ = '00017';

    /**
     * All payment direct plus action listed
     */
    const PBX_PAYMENT_ACTION_AUTHORIZE_ON_SUBSCRIBER = '00051';
    const PBX_PAYMENT_ACTION_DEBIT_ON_SUBSCRIBER = '00052';
    const PBX_PAYMENT_ACTION_AUTHORIZE_CAPTURE_ON_SUBSCRIBER = '00053';
    const PBX_PAYMENT_ACTION_CREDIT_ON_SUBSCRIBER = '00054';
    const PBX_PAYMENT_ACTION_CANCEL_ON_SUBSCRIBER = '00055';
    const PBX_PAYMENT_ACTION_REGISTER_SUBSCRIBER = '00056';
    const PBX_PAYMENT_ACTION_UPDATE_EXIST_SUBSRIBER = '00057';
    const PBX_PAYMENT_ACTION_DELETE_SUBSCRIBER = '00058';
    const PBX_PAYMENT_ACTION_TRANSACTION_WHITOUT_AUTHORIZE_FORCE = '00061';

    /**
     * $transactionNumber NUMTRANS returned by Authorize
     * @var string
     */
    protected $transactionNumber;

    /**
     * $callNumber NUMAPPEL returned by Authorize
     * @var string
     */
    protected $callNumber;

    /**
     * [generateRequestNumber description]
     * @return [type] [description]
     */
    protected function generateRequestNumber()
    {
        $secs = (date('G') * 3600) + (date('i') * 60) + date('s');
        return (string) $secs . rand(10, 99);
    }

    /**
     * authorizeAction
     * @return array response      Paybox Authorization client
     */
    public function authorizeAction()
    {
        $paybox = $this->get('lexik_paybox.direc_plus.request_handler');
        $parameters = [
            'VERSION'               => self::VERSION_DIRECT_PLUS,
            'TYPE'                  => self::PBX_PAYMENT_ACTION_AUTHORIZE,
            'DATEQ'                 => sprintf('%014d', time()),
            'NUMQUESTION'           => $this->generateRequestNumber(),
            'MONTANT'               => sprintf('%010d', str_pad(100 * 100, 10, '0', STR_PAD_LEFT)),
            'DEVISE'                => self::PBX_DEVISE_EURO,
            'REFERENCE'             => 'ORDER' . rand(1000, 9999),
            'PORTEUR'               => sprintf('%016d', '1111222233334444'),
            'DATEVAL'               => sprintf('%04d', '0117'),
            'CVV'                   => sprintf('%03d', '123'),
            'ACTIVITE'              => self::PBX_ACTIVITE_VALUE,
            'DIFFERE'               => sprintf('%03d', '001'),
        ];

        if ($this->id3d) {
            $parameters['ID3D'] = $this->id3d;
        }

        $paybox->setParameters($parameters);

        $response = array_map("utf8_encode", $paybox->send());

        if (isset($response['NUMTRANS']) && isset($response['NUMAPPEL'])) {
            $this->transactionNumber = $response['NUMTRANS'];
            $this->callNumber = $response['NUMTRANS'];
        }

        return $this->render(
            'LexikPayboxBundle:DirectPlus:index.html.twig',
            array(
                'response' => $response,
            )
        );
    }

    /**
     * captureAction
     * @return array response    Paybox debit client
     */
    public function captureAction()
    {
        $paybox = $this->get('lexik_paybox.direc_plus.request_handler');
        $parameters = [
            'VERSION'               => self::VERSION_DIRECT_PLUS,
            'TYPE'                  => self::PBX_PAYMENT_ACTION_DEBIT,
            'NUMQUESTION'           => $this->generateRequestNumber(),
            'MONTANT'               => sprintf('%010d', str_pad(100 * 100, 10, '0', STR_PAD_LEFT)),
            'DEVISE'                => self::PBX_DEVISE_EURO,
            'REFERENCE'             => 'ORDER' . rand(1000, 9999),
            'DATEQ'                 => sprintf('%014d', time()),
            'NUMTRANS'              => $this->transactionNumber,
            'NUMAPPEL'              => $this->callNumber,
            'ACTIVITE'              => self::PBX_ACTIVITE_VALUE,
            'DIFFERE'               => sprintf('%03d', '001'),
        ];

        if ($this->id3d) {
            $parameters['ID3D'] = $this->id3d;
        }

        $paybox->setParameters($parameters);
        $response = array_map("utf8_encode", $paybox->send());

        return $this->render(
            'LexikPayboxBundle:DirectPlus:index.html.twig',
            array(
                'response' => $response,
            )
        );
    }

    public function authorizeCaptureAction()
    {
        $paybox = $this->get('lexik_paybox.direc_plus.request_handler');
        $parameters = [
            'VERSION'               => self::VERSION_DIRECT_PLUS,
            'TYPE'                  => self::PBX_PAYMENT_ACTION_AUTHORIZE_CAPTURE,
            'DATEQ'                 => sprintf('%014d', time()),
            'NUMQUESTION'           => $this->generateRequestNumber(),
            'MONTANT'               => sprintf('%010d', str_pad(100 * 100, 10, '0', STR_PAD_LEFT)),
            'DEVISE'                => self::PBX_DEVISE_EURO,
            'REFERENCE'             => 'ORDER' . rand(1000, 9999),
            'PORTEUR'               => sprintf('%016d', '1111222233334444'),
            'DATEVAL'               => sprintf('%04d', '0117'),
            'CVV'                   => sprintf('%03d', '123'),
            'ACTIVITE'              => self::PBX_ACTIVITE_VALUE,
            'DIFFERE'               => sprintf('%03d', '001'),
        ];

        if ($this->id3d) {
            $parameters['ID3D'] = $this->id3d;
        }

        $paybox->setParameters($parameters);
        $response = array_map("utf8_encode", $paybox->send());

        return $this->render(
            'LexikPayboxBundle:DirectPlus:index.html.twig',
            array(
                'response' => $response,
            )
        );
    }

    public function creditAction()
    {
        $paybox = $this->get('lexik_paybox.direc_plus.request_handler');
        $parameters = [
            'VERSION'               => self::VERSION_DIRECT_PLUS,
            'TYPE'                  => self::PBX_PAYMENT_ACTION_CREDIT,
            'NUMQUESTION'           => $this->generateRequestNumber(),
            'MONTANT'               => sprintf('%010d', str_pad(100 * 100, 10, '0', STR_PAD_LEFT)),
            'DEVISE'                => self::PBX_DEVISE_EURO,
            'REFERENCE'             => 'ORDER' . rand(1000, 9999),
            'PORTEUR'               => sprintf('%016d', '1111222233334444'),
            'DATEVAL'               => sprintf('%04d', '0117'),
            'CVV'                   => sprintf('%03d', '123'),
            'DATEQ'                 => sprintf('%014d', time()),
            'NUMTRANS'              => $this->transactionNumber,
            'NUMAPPEL'              => $this->callNumber,
            'ACTIVITE'              => self::PBX_ACTIVITE_VALUE,
        ];

        $paybox->setParameters($parameters);
        $response = array_map("utf8_encode", $paybox->send());

        return $this->render(
            'LexikPayboxBundle:DirectPlus:index.html.twig',
            array(
                'response' => $response,
            )
        );
    }

    public function cancellationAction()
    {
        $paybox = $this->get('lexik_paybox.direc_plus.request_handler');
        $parameters = [
            'VERSION'               => self::VERSION_DIRECT_PLUS,
            'TYPE'                  => self::PBX_PAYMENT_ACTION_CANCELLATION,
            'NUMQUESTION'           => $this->generateRequestNumber(),
            'MONTANT'               => sprintf('%010d', str_pad(100 * 100, 10, '0', STR_PAD_LEFT)),
            'DEVISE'                => self::PBX_DEVISE_EURO,
            'REFERENCE'             => 'ORDER' . rand(1000, 9999),
            'DATEQ'                 => sprintf('%014d', time()),
            'NUMTRANS'              => $this->transactionNumber,
            'NUMAPPEL'              => $this->callNumber,
            'ACTIVITE'              => self::PBX_ACTIVITE_VALUE,
        ];

        $paybox->setParameters($parameters);
        $response = array_map("utf8_encode", $paybox->send());

        return $this->render(
            'LexikPayboxBundle:DirectPlus:index.html.twig',
            array(
                'response' => $response,
            )
        );
    }

    public function checkExistTransactionAction()
    {
        $paybox = $this->get('lexik_paybox.direc_plus.request_handler');
        $parameters = [
            'VERSION'               => self::VERSION_DIRECT_PLUS,
            'TYPE'                  => self::PBX_PAYMENT_ACTION_CHECK_EXIST_TRANSACTION,
            'NUMQUESTION'           => $this->generateRequestNumber(),
            'MONTANT'               => sprintf('%010d', str_pad(100 * 100, 10, '0', STR_PAD_LEFT)),
            'DEVISE'                => self::PBX_DEVISE_EURO,
            'REFERENCE'             => 'ORDER' . rand(1000, 9999),
            'DATEQ'                 => sprintf('%014d', time()),
            'ACTIVITE'              => self::PBX_ACTIVITE_VALUE,
        ];

        $paybox->setParameters($parameters);
        $response = array_map("utf8_encode", $paybox->send());

        return $this->render(
            'LexikPayboxBundle:DirectPlus:index.html.twig',
            array(
                'response' => $response,
            )
        );
    }

    public function transactionWhitoutAuthorizeAction()
    {
        $paybox = $this->get('lexik_paybox.direc_plus.request_handler');
        $parameters = [
            'VERSION'               => self::VERSION_DIRECT_PLUS,
            'TYPE'                  => self::PBX_PAYMENT_ACTION_TRANSACTION_WHITOUT_AUTHORIZE,
            'DATEQ'                 => sprintf('%014d', time()),
            'NUMQUESTION'           => $this->generateRequestNumber(),
            'MONTANT'               => sprintf('%010d', str_pad(100 * 100, 10, '0', STR_PAD_LEFT)),
            'DEVISE'                => self::PBX_DEVISE_EURO,
            'REFERENCE'             => 'ORDER' . rand(1000, 9999),
            'PORTEUR'               => sprintf('%016d', '1111222233334444'),
            'DATEVAL'               => sprintf('%04d', '0117'),
            'CVV'                   => sprintf('%03d', '123'),
            'ACTIVITE'              => self::PBX_ACTIVITE_VALUE,
        ];

        $paybox->setParameters($parameters);
        $response = array_map("utf8_encode", $paybox->send());

        return $this->render(
            'LexikPayboxBundle:DirectPlus:index.html.twig',
            array(
                'response' => $response,
            )
        );
    }

    public function updateAmountTransactionAction()
    {
        $paybox = $this->get('lexik_paybox.direc_plus.request_handler');
        $parameters = [
            'VERSION'               => self::VERSION_DIRECT_PLUS,
            'TYPE'                  => self::PBX_PAYMENT_ACTION_UPDATE_AMOUNT_TRANSACTION,
            'NUMQUESTION'           => $this->generateRequestNumber(),
            'MONTANT'               => sprintf('%010d', str_pad(100 * 100, 10, '0', STR_PAD_LEFT)),
            'DEVISE'                => self::PBX_DEVISE_EURO,
            'DATEQ'                 => sprintf('%014d', time()),
            'NUMTRANS'              => $this->transactionNumber,
            'NUMAPPEL'              => $this->callNumber,
            'ACTIVITE'              => self::PBX_ACTIVITE_VALUE
        ];

        $paybox->setParameters($parameters);
        $response = array_map("utf8_encode", $paybox->send());

        return $this->render(
            'LexikPayboxBundle:DirectPlus:index.html.twig',
            array(
                'response' => $response,
            )
        );
    }

    public function refundAction()
    {
        $paybox = $this->get('lexik_paybox.direc_plus.request_handler');
        $parameters = [
            'VERSION'               => self::VERSION_DIRECT_PLUS,
            'TYPE'                  => self::PBX_PAYMENT_ACTION_REFUND,
            'NUMQUESTION'           => $this->generateRequestNumber(),
            'MONTANT'               => sprintf('%010d', str_pad(100 * 100, 10, '0', STR_PAD_LEFT)),
            'DEVISE'                => self::PBX_DEVISE_EURO,
            'DATEQ'                 => sprintf('%014d', time()),
            'NUMTRANS'              => $this->transactionNumber,
            'NUMAPPEL'              => $this->callNumber,
            'ACTIVITE'              => self::PBX_ACTIVITE_VALUE,
        ];

        $paybox->setParameters($parameters);
        $response = array_map("utf8_encode", $paybox->send());

        return $this->render(
            'LexikPayboxBundle:DirectPlus:index.html.twig',
            array(
                'response' => $response,
            )
        );
    }

    public function readAction()
    {
        $paybox = $this->get('lexik_paybox.direc_plus.request_handler');
        $parameters = [
            'VERSION'               => self::VERSION_DIRECT_PLUS,
            'TYPE'                  => self::PBX_PAYMENT_ACTION_READ,
            'NUMQUESTION'           => $this->generateRequestNumber(),
            'DEVISE'                => self::PBX_DEVISE_EURO,
            'DATEQ'                 => sprintf('%014d', time()),
            'NUMTRANS'              => $this->transactionNumber,
            'ACTIVITE'              => self::PBX_ACTIVITE_VALUE,
        ];

        $paybox->setParameters($parameters);
        $response = array_map("utf8_encode", $paybox->send());

        return $this->render(
            'LexikPayboxBundle:DirectPlus:index.html.twig',
            array(
                'response' => $response,
            )
        );
    }

    public function authorizeOnSubscriberAction()
    {
        $paybox = $this->get('lexik_paybox.direc_plus.request_handler');
        $parameters = [
            'VERSION'               => self::VERSION_DIRECT_PLUS,
            'TYPE'                  => self::PBX_PAYMENT_ACTION_AUTHORIZE_ON_SUBSCRIBER,
            'DATEQ'                 => sprintf('%014d', time()),
            'NUMQUESTION'           => $this->generateRequestNumber(),
            'MONTANT'               => sprintf('%010d', str_pad(100 * 100, 10, '0', STR_PAD_LEFT)),
            'DEVISE'                => self::PBX_DEVISE_EURO,
            'REFERENCE'             => 'ORDER' . rand(1000, 9999),
            'PORTEUR'               => sprintf('%016d', 'REFABONNE' . rand(1111, 9999)),
            'DATEVAL'               => sprintf('%04d', '0117'),
            'REFABONNE'             => str_replace('-', '', 'REFABONNE' . rand(1111, 9999)),
            'ACTIVITE'              => self::PBX_ACTIVITE_VALUE,
            'DIFFERE'               => sprintf('%03d', '001')
        ];

        if ($this->id3d) {
            $parameters['ID3D'] = $this->id3d;
        }

        $paybox->setParameters($parameters);

        $response = array_map("utf8_encode", $paybox->send());

        if (isset($response['NUMTRANS']) && isset($response['NUMAPPEL'])) {
            $this->transactionNumber = $response['NUMTRANS'];
            $this->callNumber = $response['NUMTRANS'];
        }

        return $this->render(
            'LexikPayboxBundle:DirectPlus:index.html.twig',
            array(
                'response' => $response,
            )
        );
    }

    public function captureOnSubscriberAction()
    {
        $paybox = $this->get('lexik_paybox.direc_plus.request_handler');
        $parameters = [
            'VERSION'               => self::VERSION_DIRECT_PLUS,
            'TYPE'                  => self::PBX_PAYMENT_ACTION_DEBIT_ON_SUBSCRIBER,
            'NUMQUESTION'           => $this->generateRequestNumber(),
            'MONTANT'               => sprintf('%010d', str_pad(100 * 100, 10, '0', STR_PAD_LEFT)),
            'DEVISE'                => self::PBX_DEVISE_EURO,
            'REFERENCE'             => 'ORDER' . rand(1000, 9999),
            'DATEQ'                 => sprintf('%014d', time()),
            'REFABONNE'             => str_replace('-', '', 'REFABONNE' . rand(1111, 9999)),
            'NUMTRANS'              => $this->transactionNumber,
            'NUMAPPEL'              => $this->callNumber,
            'ACTIVITE'              => self::PBX_ACTIVITE_VALUE,
            'DIFFERE'               => sprintf('%03d', '001'),
        ];

        $paybox->setParameters($parameters);
        $response = array_map("utf8_encode", $paybox->send());

        return $this->render(
            'LexikPayboxBundle:DirectPlus:index.html.twig',
            array(
                'response' => $response,
            )
        );
    }

    public function authorizeCaptureOnSubscriberAction()
    {
        $paybox = $this->get('lexik_paybox.direc_plus.request_handler');
        $parameters = array(
            'VERSION'               => self::VERSION_DIRECT_PLUS,
            'TYPE'                  => self::PBX_PAYMENT_ACTION_AUTHORIZE_CAPTURE_ON_SUBSCRIBER,
            'NUMQUESTION'           => $this->generateRequestNumber(),
            'MONTANT'               => sprintf('%010d', str_pad(100 * 100, 10, '0', STR_PAD_LEFT)),
            'DEVISE'                => self::PBX_DEVISE_EURO,
            'REFERENCE'             => 'ORDER' . rand(1000, 9999),
            'PORTEUR'               => sprintf('%016d', 'saz3Esd125DZ'),
            'DATEVAL'               => sprintf('%04d', '0117'),
            'REFABONNE'             => str_replace('-', '', 'REFABONNE' . rand(1111, 9999)),
            'DATEQ'                 => sprintf('%014d', time()),
            'DIFFERE'               => sprintf('%03d', '001'),
        );

        $paybox->setParameters($parameters);
        $response = array_map("utf8_encode", $paybox->send());

        return $this->render(
            'LexikPayboxBundle:DirectPlus:index.html.twig',
            array(
                'response' => $response,
            )
        );
    }

    public function creditOnSubscriberAction()
    {
        $paybox = $this->get('lexik_paybox.direc_plus.request_handler');
        $parameters = [
            'VERSION'               => self::VERSION_DIRECT_PLUS,
            'TYPE'                  => self::PBX_PAYMENT_ACTION_CREDIT_ON_SUBSCRIBER,
            'NUMQUESTION'           => $this->generateRequestNumber(),
            'MONTANT'               => sprintf('%010d', str_pad(100 * 100, 10, '0', STR_PAD_LEFT)),
            'DEVISE'                => self::PBX_DEVISE_EURO,
            'REFERENCE'             => 'ORDER' . rand(1000, 9999),
            'DATEQ'                 => sprintf('%014d', time()),
            'PORTEUR'               => sprintf('%016d', 'saz3Esd125DZ'),
            'DATEVAL'               => sprintf('%04d', '0117'),
            'REFABONNE'             => str_replace('-', '', 'REFABONNE' . rand(1111, 9999)),
        ];

        $paybox->setParameters($parameters);
        $response = array_map("utf8_encode", $paybox->send());

        return $this->render(
            'LexikPayboxBundle:DirectPlus:index.html.twig',
            array(
                'response' => $response,
            )
        );
    }

    public function cancelOnSubscriberAction()
    {
        $paybox = $this->get('lexik_paybox.direc_plus.request_handler');
        $parameters = [
            'VERSION'               => self::VERSION_DIRECT_PLUS,
            'TYPE'                  => self::PBX_PAYMENT_ACTION_CANCEL_ON_SUBSCRIBER,
            'NUMQUESTION'           => $this->generateRequestNumber(),
            'MONTANT'               => sprintf('%010d', str_pad(100 * 100, 10, '0', STR_PAD_LEFT)),
            'DEVISE'                => self::PBX_DEVISE_EURO,
            'REFERENCE'             => 'ORDER' . rand(1000, 9999),
            'DATEQ'                 => sprintf('%014d', time()),
            'PORTEUR'               => sprintf('%016d', 'saz3Esd125DZ'),
            'DATEVAL'               => sprintf('%04d', '0117'),
            'REFABONNE'             => str_replace('-', '', 'REFABONNE' . rand(1111, 9999)),
            'NUMTRANS'              => $this->transactionNumber,
            'NUMAPPEL'              => $this->callNumber,
        ];

        $paybox->setParameters($parameters);
        $response = array_map("utf8_encode", $paybox->send());

        return $this->render(
            'LexikPayboxBundle:DirectPlus:index.html.twig',
            array(
                'response' => $response,
            )
        );
    }

    public function registerNewSubscriberAction()
    {
        $paybox = $this->get('lexik_paybox.direc_plus.request_handler');
        $parameters = [
            'VERSION'               => self::VERSION_DIRECT_PLUS,
            'TYPE'                  => self::PBX_PAYMENT_ACTION_REGISTER_SUBSCRIBER,
            'NUMQUESTION'           => $this->generateRequestNumber(),
            'MONTANT'               => sprintf('%010d', str_pad(100 * 100, 10, '0', STR_PAD_LEFT)),
            'DEVISE'                => self::PBX_DEVISE_EURO,
            'REFERENCE'             => 'ORDER' . rand(1000, 9999),
            'PORTEUR'               => sprintf('%016d', '1111222233334444'),
            'DATEVAL'               => sprintf('%04d', '0117'),
            'CVV'                   => sprintf('%03d', '123'),
            'REFABONNE'             => str_replace('-', '', str_replace('-', '', 'REFABONNE' . rand(1111, 9999))),
            'ACTIVITE'              => self::PBX_ACTIVITE_VALUE,
            'DATEQ'                 => sprintf('%014d', time()),
        ];
        $paybox->setParameters($parameters);
        $response = array_map("utf8_encode", $paybox->send());

        return $this->render(
            'LexikPayboxBundle:DirectPlus:index.html.twig',
            array(
                'response' => $response,
            )
        );
    }

    public function updateExistSubscriberAction()
    {
        $paybox = $this->get('lexik_paybox.direc_plus.request_handler');
        $parameters = [
            'VERSION'               => self::VERSION_DIRECT_PLUS,
            'TYPE'                  => self::PBX_PAYMENT_ACTION_UPDATE_EXIST_SUBSRIBER,
            'NUMQUESTION'           => $this->generateRequestNumber(),
            'MONTANT'               => sprintf('%010d', str_pad(100 * 100, 10, '0', STR_PAD_LEFT)),
            'DEVISE'                => self::PBX_DEVISE_EURO,
            'DATEQ'                 => sprintf('%014d', time()),
            'PORTEUR'               => sprintf('%016d', 'saz3Esd125DZ'),
            'DATEVAL'               => sprintf('%04d', '0117'),
            'REFABONNE'             => str_replace('-', '', 'REFABONNE' . rand(1111, 9999)),
            'ACTIVITE'              => self::PBX_ACTIVITE_VALUE
        ];

        $paybox->setParameters($parameters);
        $response = array_map("utf8_encode", $paybox->send());

        return $this->render(
            'LexikPayboxBundle:DirectPlus:index.html.twig',
            array(
                'response' => $response,
            )
        );
    }

    public function deleteSubscriberAction()
    {
        $paybox = $this->get('lexik_paybox.direc_plus.request_handler');
        $parameters = [
            'VERSION'               => self::VERSION_DIRECT_PLUS,
            'TYPE'                  => self::PBX_PAYMENT_ACTION_DELETE_SUBSCRIBER,
            'NUMQUESTION'           => $this->generateRequestNumber(),
            'DEVISE'                => self::PBX_DEVISE_EURO,
            'DATEQ'                 => sprintf('%014d', time()),
            'REFABONNE'             => str_replace('-', '', 'REFABONNE' . rand(1111, 9999)),
            'ACTIVITE'              => self::PBX_ACTIVITE_VALUE
        ];

        $paybox->setParameters($parameters);
        $response = array_map("utf8_encode", $paybox->send());

        return $this->render(
            'LexikPayboxBundle:DirectPlus:index.html.twig',
            array(
                'response' => $response,
            )
        );
    }

    public function transactionWhitoutAuthorizeForceAction()
    {
        $paybox = $this->get('lexik_paybox.direc_plus.request_handler');
        $parameters = [
            'VERSION'               => self::VERSION_DIRECT_PLUS,
            'TYPE'                  => self::PBX_PAYMENT_ACTION_TRANSACTION_WHITOUT_AUTHORIZE_FORCE,
            'NUMQUESTION'           => $this->generateRequestNumber(),
            'MONTANT'               => sprintf('%010d', str_pad(100 * 100, 10, '0', STR_PAD_LEFT)),
            'DEVISE'                => self::PBX_DEVISE_EURO,
            'REFERENCE'             => 'ORDER' . rand(1000, 9999),
            'DATEQ'                 => sprintf('%014d', time()),
            'PORTEUR'               => sprintf('%016d', 'saz3Esd125DZ'),
            'DATEVAL'               => sprintf('%04d', '0117'),
            'REFABONNE'             => str_replace('-', '', 'REFABONNE' . rand(1111, 9999)),
            'ACTIVITE'              => self::PBX_ACTIVITE_VALUE
        ];

        $paybox->setParameters($parameters);
        $response = array_map("utf8_encode", $paybox->send());

        return $this->render(
            'LexikPayboxBundle:DirectPlus:index.html.twig',
            array(
                'response' => $response,
            )
        );
    }
}
