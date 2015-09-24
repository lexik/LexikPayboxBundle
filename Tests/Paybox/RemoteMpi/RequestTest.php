<?php

namespace Lexik\Bundle\PayboxBundle\Tests\Paybox\RemoteMPI;

use Lexik\Bundle\PayboxBundle\Paybox\RemoteMPI\Request;
use Lexik\Bundle\PayboxBundle\Transport\BuzzTransport;
use Lexik\Bundle\PayboxBundle\Transport\CurlTransport;

/**
 * Class RequestTest
 *
 * @package Lexik\Bundle\PayboxBundle\Tests\Paybox\RemoteMpi
 *
 *  @author Romain Marecat <romain.marecat@gmail.com>
 */
class RequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @param array  $messages
     * @param string $httpResponse
     * @param bool   $dispatch
     */
    protected function initMock(array $messages, $httpResponse = null, $dispatch = false)
    {
        $parameters = array(
            'site'       => '1999888',
            'rang'       => '063',
            'cle'        => '1999888I',
            'production' => false,
            'currencies' => array(
                '036',
                '124',
                '756',
                '826',
                '840',
                '978',
            ),
        );

        $servers = array(
            'remote_mpi' => array(
                'primary' => array(
                    'protocol'    => 'https',
                    'host'        => 'tpeweb.paybox.com',
                    'system_path' => '/cgi/MYchoix_pagepaiement.cgi',
                    'test_path'   => '/load.html',
                ),
                'secondary' => array(
                    'protocol'    => 'https',
                    'host'        => 'tpeweb1.paybox.com',
                    'system_path' => '/cgi/MYchoix_pagepaiement.cgi',
                    'test_path'   => '/load.html',
                ),
                'preprod' => array(
                    'protocol'    => 'https',
                    'host'        => 'preprod-tpeweb.paybox.com',
                    'system_path' => '/cgi/MYchoix_pagepaiement.cgi',
                    'test_path'   => '/load.html',
                ),
            ),
        );

        $logger = $this->getMock('Psr\Log\LoggerInterface');
        foreach ($messages as $i => $message) {
            $logger
                ->expects($this->at($i))
                ->method($message[0])
                ->with(new \PHPUnit_Framework_Constraint_StringMatches($message[1]))
            ;
        }

        $dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcher');
        if (true === $dispatch) {
            $dispatcher
                ->expects($this->once())
                ->method('dispatch')
            ;
        }

        $transport = $this->getMockForAbstractClass('Lexik\Bundle\PayboxBundle\Transport\AbstractTransport');
        if (null !== $httpResponse) {
            $transport
                ->expects($this->once())
                ->method('call')
                ->will($this->returnValue($httpResponse))
            ;
        }

        /**
         * @wtf Shut... You haven't seen the lines below, ok ?
         */
        // $transport = new CurlTransport();
        // $transport = new BuzzTransport();

        $this->request = new Request($parameters, $servers, $logger, $dispatcher, $transport);
    }

    public function tearDown()
    {
        $this->request = null;
    }

    public function testSimpleAuthentication()
    {
        $time = time();

        $this->initMock(
            array(
                array('info', 'New API call.'),
                array('info', 'Url : https://preprod-ppps.paybox.com/PPPS.php'),
                array('info', 'Data :'),
                array('info', ' > VERSION = 00104'),
                array('info', ' > SITE = 1999888'),
                array('info', ' > RANG = 032'),
                array('info', ' > CLE = 1999888I'),
                array('info', ' > TYPE = 00001'),
                array('info', ' > NUMQUESTION = ' . sprintf('%010d', $time)),
                array('info', ' > MONTANT = 0000001000'),
                array('info', ' > DEVISE = 978'),
                array('info', ' > REFERENCE = TestPaybox'),
                array('info', ' > ACTIVITE = 024'),
                array('info', ' > DATEQ = ' . sprintf('%014s', $time)),
                array('info', 'Result : NUMTRANS=%s&NUMAPPEL=%s&NUMQUESTION='.sprintf('%010d', $time).'&SITE=1999888&RANG=32&AUTORISATION=XXXXXX&CODEREPONSE=00000&COMMENTAIRE=%s&REFABONNE=&PORTEUR='),
            ),
            'NUMTRANS=0005329117&NUMAPPEL=0010244812&NUMQUESTION='.sprintf('%010d', $time).'&SITE=1999888&RANG=32&AUTORISATION=XXXXXX&CODEREPONSE=00000&COMMENTAIRE=Demande trait�e avec succ�s&REFABONNE=&PORTEUR=',
            true
        );

        $parameters([
            'Amount'                => '100',
            'CCExpDate'             => '0117',
            'CCNumber'              => '1111222233334444',
            'Currency'              => '978',
            'CVVCode'               => '123',
            'IdSession'             => 'ORDER' . rand(1000, 9999),
            'URLHttpDirect'         => 'https://github.com/lexik/LexikPayboxBundle',
            'URLRetour'             => 'https://github.com/lexik/LexikPayboxBundle',
        ]);

        $this->request->setParameters($parameters);
        $result = $this->request->send();

        $this->assertNotEmpty($result['ID3D']);
        $this->assertEquals('00000', $result['']);
        $this->assertEquals('XXXXXX', $result['AUTORISATION']);
    }
}
