<?php

namespace Lexik\Bundle\PayboxBundle\Tests\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\ParameterBag;

use Lexik\Bundle\PayboxBundle\Service\Paybox;
use Lexik\Bundle\PayboxBundle\Service\PayboxSystemResponse;

/**
 * PayboxSystemResponse's test class.
 */
class PayboxSystemResponseTest extends \PHPUnit_Framework_TestCase
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

        $this->_response = new PayboxSystemResponse($request, $logger, $dispatcher);
    }

    protected function tearDown()
    {
        $this->_response = null;
    }

    /**
     * @covers PayboxSystemRequest::verifySignature
     * @covers PayboxSystemRequest::initData
     * @covers PayboxSystemRequest::initSignature
     * @covers PayboxSystemRequest::getRequestParameters
     */
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

    /**
     * @covers PayboxSystemRequest::verifySignature
     * @covers PayboxSystemRequest::initData
     * @covers PayboxSystemRequest::initSignature
     * @covers PayboxSystemRequest::getRequestParameters
     */
    public function testVerifySignatureValidParameter()
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
            array('info', 'Signature is invalid.'),
        ));

        $this->assertFalse($this->_response->verifySignature());
    }

    /**
     * @covers PayboxSystemRequest::verifySignature
     * @covers PayboxSystemRequest::initData
     * @covers PayboxSystemRequest::initSignature
     * @covers PayboxSystemRequest::getRequestParameters
     */
    public function testVerifySignatureValidSignature()
    {
        $this->initMock(array(
            'Mt'     => '1000',
            'Ref'    => 'CMD1349338389',
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
            array('info', 'Mt=1000&Ref=CMD1349338388&Auto=XXXXXX&Erreur=00000'),
            array('info', 'INVALIDOpcbfUruOU7kKG/ZmDPsDzM31VrpMIObQomYwEE/afhJiDPkWVN9+r3JVFXBsFnqo3VlOVzxQVVahXpF7eWiJoAe5LcIoqSvms96SFFv9LfndS/3zAO5fF/tR4Us3rOSUwT1Hs2AS17R3B9ATwBMhKt1l3DPw9hClp'),
            array('info', 'Signature is invalid.'),
        ));

        $this->assertFalse($this->_response->verifySignature());
    }
}
