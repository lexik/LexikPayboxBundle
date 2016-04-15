<?php

namespace Lexik\Bundle\PayboxBundle\Tests\Paybox\System;

use Lexik\Bundle\PayboxBundle\Paybox\System\Base\Response;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Class ResponseTest
 *
 * @package Lexik\Bundle\PayboxBundle\Tests\Paybox\System
 *
 * @author Olivier Maisonneuve <o.maisonneuve@lexik.fr>
 */
class ResponseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Response
     */
    private $_response;

    /**
     * @param array $parameters
     * @param array $messages
     */
    protected function initMock(array $parameters, array $messages)
    {
        $request = $this->getMock('Symfony\Component\HttpFoundation\Request');
        $request
            ->expects($this->any())
            ->method('isMethod')
            ->with($this->equalTo('POST'))
            ->will($this->returnValue(true))
        ;

        $request->request = new ParameterBag($parameters);

        $requestStack = $this->getMock('Symfony\Component\HttpFoundation\RequestStack');
        $requestStack
            ->expects($this->once())
            ->method('getCurrentRequest')
            ->willReturn($request);

        /** @var LoggerInterface $logger */
        $logger = $this->getMock('Psr\Log\LoggerInterface');
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

        $parameters = array(
            'public_key' => __DIR__ . '/../../../../Resources/config/paybox_public_key.pem',
            'hmac' => array(
                'signature_name' => 'Sign',
            ),
        );

        $this->_response = new Response($requestStack, $logger, $dispatcher, $parameters);
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
            array('info',  'New IPN call.'),
            array('info',  'Mt=1000'),
            array('info',  'Ref=CMD1349338389'),
            array('info',  'Auto=XXXXXX'),
            array('info',  'Erreur=00000'),
            array('info',  'Sign=QnhlnuOpcbfUruOU7kKG/ZmDPsDzM31VrpMIObQomYwEE/afhJiDPkWVN9+r3JVFXBsFnqo3VlOVzxQVVahXpF7eWiJoAe5LcIoqSvms96SFFv9LfndS/3zAO5fF/tR4Us3rOSUwT1Hs2AS17R3B9ATwBMhKt1l3DPw9hClpFw0='),
            array('info',  'Mt=1000&Ref=CMD1349338389&Auto=XXXXXX&Erreur=00000'),
            array('info',  'QnhlnuOpcbfUruOU7kKG/ZmDPsDzM31VrpMIObQomYwEE/afhJiDPkWVN9+r3JVFXBsFnqo3VlOVzxQVVahXpF7eWiJoAe5LcIoqSvms96SFFv9LfndS/3zAO5fF/tR4Us3rOSUwT1Hs2AS17R3B9ATwBMhKt1l3DPw9hClpFw0='),
            array('error', 'Signature is invalid.'),
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
            'Sign'   => 'invalidpcbfUruOU7kKG/ZmDPsDzM31VrpMIObQomYwEE/afhJiDPkWVN9+r3JVFXBsFnqo3VlOVzxQVVahXpF7eWiJoAe5LcIoqSvms96SFFv9LfndS/3zAO5fF/tR4Us3rOSUwT1Hs2AS17R3B9ATwBMhKt1l3DPw9hClpFw0=',
        ), array(
            array('info', 'New IPN call.'),
            array('info', 'Mt=1000'),
            array('info', 'Ref=CMD1349338388'),
            array('info', 'Auto=XXXXXX'),
            array('info', 'Erreur=00000'),
            array('info', 'Sign=invalidpcbfUruOU7kKG/ZmDPsDzM31VrpMIObQomYwEE/afhJiDPkWVN9+r3JVFXBsFnqo3VlOVzxQVVahXpF7eWiJoAe5LcIoqSvms96SFFv9LfndS/3zAO5fF/tR4Us3rOSUwT1Hs2AS17R3B9ATwBMhKt1l3DPw9hClpFw0='),
            array('info', 'Mt=1000&Ref=CMD1349338388&Auto=XXXXXX&Erreur=00000'),
            array('info', 'invalidpcbfUruOU7kKG/ZmDPsDzM31VrpMIObQomYwEE/afhJiDPkWVN9+r3JVFXBsFnqo3VlOVzxQVVahXpF7eWiJoAe5LcIoqSvms96SFFv9LfndS/3zAO5fF/tR4Us3rOSUwT1Hs2AS17R3B9ATwBMhKt1l3DPw9hClpFw0='),
            array('error', 'Signature is invalid.'),
        ));

        $this->assertFalse($this->_response->verifySignature());
    }

    public function testVerifySignatureInvalidSignatureFormat()
    {
        $this->initMock(array(
            'Mt'     => '1000',
            'Ref'    => 'CMD1349338388',
            'Auto'   => 'XXXXXX',
            'Erreur' => '00000',
            'Sign'   => 'badformatbfUruOU7kKG/ZmDPsDzM31VrpMIObQomYwEE/afhJiDPkWVN9+r3JVFXBsFnqo3VlOVzxQVVahXpF7eWiJoAe5LcIoqSvms96SFFv9LfndS/3zAO5fF/tR4Us3rOSUwT1Hs2AS17R3B9ATwBMhKt1l3DPw9hClp',
        ), array(
            array('info', 'New IPN call.'),
            array('info', 'Mt=1000'),
            array('info', 'Ref=CMD1349338388'),
            array('info', 'Auto=XXXXXX'),
            array('info', 'Erreur=00000'),
            array('info', 'Sign=badformatbfUruOU7kKG/ZmDPsDzM31VrpMIObQomYwEE/afhJiDPkWVN9+r3JVFXBsFnqo3VlOVzxQVVahXpF7eWiJoAe5LcIoqSvms96SFFv9LfndS/3zAO5fF/tR4Us3rOSUwT1Hs2AS17R3B9ATwBMhKt1l3DPw9hClp'),
            array('error', 'Bad signature format.'),
            array('info', 'Mt=1000&Ref=CMD1349338388&Auto=XXXXXX&Erreur=00000'),
            array('info', ''),
            array('error', 'Signature is invalid.'),
        ));

        $this->assertFalse($this->_response->verifySignature());
    }

    public function testVerifySignatureValidUrlencodedSignature()
    {
        $this->initMock(array(
                'Mt'     => '8160',
                'Ref'    => 'COM000000117T1402932051',
                'Erreur' => '00004',
                'Sign'   => 'j2OZPEE0gKc%2BD34u2QkpBUR3VQyd9zYhpewHS6IZ1vjdiuGvC01irSBb2taQblYQ3RXI0DkmgIgdFY8ywW6NOdWx1vH%2B1c3GSZX2MhMqlonpfElUAN%2FlzYNH%2Ftw%2BntQLvzO2HBPobCLNUERqKrFqU9dSAvAYagXNdjpU%2BXPzPgI%3D',
            ), array(
                array('info', 'New IPN call.'),
                array('info', 'Mt=8160'),
                array('info', 'Ref=COM000000117T1402932051'),
                array('info', 'Erreur=00004'),
                array('info', 'Sign=j2OZPEE0gKc%2BD34u2QkpBUR3VQyd9zYhpewHS6IZ1vjdiuGvC01irSBb2taQblYQ3RXI0DkmgIgdFY8ywW6NOdWx1vH%2B1c3GSZX2MhMqlonpfElUAN%2FlzYNH%2Ftw%2BntQLvzO2HBPobCLNUERqKrFqU9dSAvAYagXNdjpU%2BXPzPgI%3D'),
                array('info', 'Mt=8160&Ref=COM000000117T1402932051&Erreur=00004'),
                array('info', 'j2OZPEE0gKc+D34u2QkpBUR3VQyd9zYhpewHS6IZ1vjdiuGvC01irSBb2taQblYQ3RXI0DkmgIgdFY8ywW6NOdWx1vH+1c3GSZX2MhMqlonpfElUAN/lzYNH/tw+ntQLvzO2HBPobCLNUERqKrFqU9dSAvAYagXNdjpU+XPzPgI='),
                array('info', 'Signature is valid.'),
            ));

        $this->assertTrue($this->_response->verifySignature());
    }
}
