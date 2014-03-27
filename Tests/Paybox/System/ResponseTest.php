<?php

namespace Lexik\Bundle\PayboxBundle\Tests\Paybox\System;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\ParameterBag;

use Lexik\Bundle\PayboxBundle\Paybox\System\Base\Response;

/**
 * Paybox\System\Response class tests.
 *
 * @author Olivier Maisonneuve <o.maisonneuve@lexik.fr>
 */
class ResponseTest extends \PHPUnit_Framework_TestCase
{
    private $_response;

    protected function initMock(array $parameters, array $messages)
    {
        $request = $this->getMock('Symfony\Component\HttpFoundation\Request');
        $request
            ->expects($this->any())
            ->method('isMethod')
            ->with($this->equalTo('POST'))
            ->will($this->returnValue(true))
        ;

        $bag = new ParameterBag($parameters);
        $request->request = $bag;

        $logger = $this->getMock('Symfony\Component\HttpKernel\Log\LoggerInterface');
        foreach ($messages as $i => $message) {
            $logger
                ->expects($this->at($i))
                ->method($message[0])
                ->with($this->equalTo($message[1]))
            ;
        }

        $dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcher');
        $dispatcher
            ->expects($this->once())
            ->method('dispatch')
        ;

        $publicKey = __DIR__ . '/../../../Resources/config/paybox_public_key.pem';
        $validationBy = 'url_ipn';
        $pbxRetour = array_diff(array_keys($parameters), array('Sign'));

        $this->_response = new Response($request, $logger, $dispatcher, $publicKey, $validationBy, $pbxRetour);
    }

    protected function tearDown()
    {
        $this->_response = null;
    }

    public function testVerifySignatureValid()
    {
        $this->initMock(array(
            'Mt'     => '1000',
            'Ref'    => 'CMD1349338388',
            'Auto'   => 'XXXXXX',
            'Erreur' => '00000',
            'Sign'   => 'QnhlnuOpcbfUruOU7kKG/ZmDPsDzM31VrpMIObQomYwEE/afhJiDPkWVN9+r3JVFXBsFnqo3VlOVzxQVVahXpF7eWiJoAe5LcIoqSvms96SFFv9LfndS/3zAO5fF/tR4Us3rOSUwT1Hs2AS17R3B9ATwBMhKt1l3DPw9hClpFw0=',
        ), array(
            array('info', 'New IPN call.'),
            array('info', 'Mt=1000'),
            array('info', 'Ref=CMD1349338388'),
            array('info', 'Auto=XXXXXX'),
            array('info', 'Erreur=00000'),
            array('info', 'Sign=QnhlnuOpcbfUruOU7kKG/ZmDPsDzM31VrpMIObQomYwEE/afhJiDPkWVN9+r3JVFXBsFnqo3VlOVzxQVVahXpF7eWiJoAe5LcIoqSvms96SFFv9LfndS/3zAO5fF/tR4Us3rOSUwT1Hs2AS17R3B9ATwBMhKt1l3DPw9hClpFw0='),
            array('info', 'Mt=1000&Ref=CMD1349338388&Auto=XXXXXX&Erreur=00000'),
            array('info', 'QnhlnuOpcbfUruOU7kKG/ZmDPsDzM31VrpMIObQomYwEE/afhJiDPkWVN9+r3JVFXBsFnqo3VlOVzxQVVahXpF7eWiJoAe5LcIoqSvms96SFFv9LfndS/3zAO5fF/tR4Us3rOSUwT1Hs2AS17R3B9ATwBMhKt1l3DPw9hClpFw0='),
            array('info', 'Signature is valid.'),
        ));

        $this->assertTrue($this->_response->verifySignature());
    }

    public function testVerifySignatureInvalidParameter()
    {
        $this->initMock(array(
            'Mt'     => '1000',
            'Ref'    => 'CMD1349338389',
            'Auto'   => 'XXXXXX',
            'Erreur' => '00000',
            'Sign'   => 'QnhlnuOpcbfUruOU7kKG/ZmDPsDzM31VrpMIObQomYwEE/afhJiDPkWVN9+r3JVFXBsFnqo3VlOVzxQVVahXpF7eWiJoAe5LcIoqSvms96SFFv9LfndS/3zAO5fF/tR4Us3rOSUwT1Hs2AS17R3B9ATwBMhKt1l3DPw9hClpFw0=',
        ), array(
            array('info', 'New IPN call.'),
            array('info', 'Mt=1000'),
            array('info', 'Ref=CMD1349338389'),
            array('info', 'Auto=XXXXXX'),
            array('info', 'Erreur=00000'),
            array('info', 'Sign=QnhlnuOpcbfUruOU7kKG/ZmDPsDzM31VrpMIObQomYwEE/afhJiDPkWVN9+r3JVFXBsFnqo3VlOVzxQVVahXpF7eWiJoAe5LcIoqSvms96SFFv9LfndS/3zAO5fF/tR4Us3rOSUwT1Hs2AS17R3B9ATwBMhKt1l3DPw9hClpFw0='),
            array('info', 'Mt=1000&Ref=CMD1349338389&Auto=XXXXXX&Erreur=00000'),
            array('info', 'QnhlnuOpcbfUruOU7kKG/ZmDPsDzM31VrpMIObQomYwEE/afhJiDPkWVN9+r3JVFXBsFnqo3VlOVzxQVVahXpF7eWiJoAe5LcIoqSvms96SFFv9LfndS/3zAO5fF/tR4Us3rOSUwT1Hs2AS17R3B9ATwBMhKt1l3DPw9hClpFw0='),
            array('err',  'Signature is invalid.'),
        ));

        $this->assertFalse($this->_response->verifySignature());
    }

    public function testVerifySignatureInvalidSignature()
    {
        $this->initMock(array(
            'Mt'     => '1000',
            'Ref'    => 'CMD1349338388',
            'Auto'   => 'XXXXXX',
            'Erreur' => '00000',
            'Sign'   => 'INVALIDOpcbfUruOU7kKG/ZmDPsDzM31VrpMIObQomYwEE/afhJiDPkWVN9+r3JVFXBsFnqo3VlOVzxQVVahXpF7eWiJoAe5LcIoqSvms96SFFv9LfndS/3zAO5fF/tR4Us3rOSUwT1Hs2AS17R3B9ATwBMhKt1l3DPw9hClp',
        ), array(
            array('info', 'New IPN call.'),
            array('info', 'Mt=1000'),
            array('info', 'Ref=CMD1349338388'),
            array('info', 'Auto=XXXXXX'),
            array('info', 'Erreur=00000'),
            array('info', 'Sign=INVALIDOpcbfUruOU7kKG/ZmDPsDzM31VrpMIObQomYwEE/afhJiDPkWVN9+r3JVFXBsFnqo3VlOVzxQVVahXpF7eWiJoAe5LcIoqSvms96SFFv9LfndS/3zAO5fF/tR4Us3rOSUwT1Hs2AS17R3B9ATwBMhKt1l3DPw9hClp'),
            array('err',  'Bad signature format.'),
            array('info', 'Mt=1000&Ref=CMD1349338388&Auto=XXXXXX&Erreur=00000'),
            array('info', ''),
            array('err',  'Signature is invalid.'),
        ));

        $this->assertFalse($this->_response->verifySignature());
    }
}
