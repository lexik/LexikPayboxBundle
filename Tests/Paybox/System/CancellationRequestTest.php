<?php

namespace Lexik\Bundle\PayboxBundle\Tests\Paybox\System;

use Symfony\Component\Form\FormFactoryInterface;

use Lexik\Bundle\PayboxBundle\Paybox\System\CancellationRequest;
use Lexik\Bundle\PayboxBundle\Transport\CurlTransport;

/**
 * Paybox\System\CancellationRequest class tests.
 *
 * @author Fabien Pomerol <fabien.pomerol@gmail.com>
 */
class CancellationRequestTest extends \PHPUnit_Framework_TestCase
{
    private $_paybox;

    public function testInitParameters()
    {
        $this->assertEquals('1999888', $this->_paybox->getParameter('site'));
        $this->assertEquals('032', $this->_paybox->getParameter('mach'));
        $this->assertEquals(2, $this->_paybox->getParameter('identifiant'));
    }

    public function testSetParameter()
    {
        $this->_paybox->setParameter('reference', 'Test');
        $this->_paybox->setParameter('REFERENCE', 'Test');

        $this->assertEquals($this->_paybox->getParameter('REFERENCE'), 'Test');
        $this->assertEquals($this->_paybox->getParameter('reference'), 'Test');

        $this->assertNull($this->_paybox->getParameter('PBX_DISPLAY'));
    }

    public function testSetParameters()
    {
        $this->_paybox->setParameters(array(
            'REFERENCE'    => 'Test',
            'ABONNEMENT' => 'Test',
        ));

        $this->assertEquals($this->_paybox->getParameter('ABONNEMENT'), 'Test');
        $this->assertEquals($this->_paybox->getParameter('reference'), 'Test');
    }

    public function testGetParameters()
    {
        $this->assertTrue(null === $this->_paybox->getParameter('TIME'));
        $this->assertTrue(null === $this->_paybox->getParameter('HMAC'));

        $parameters = $this->_paybox->getParameters();

        $this->assertTrue(isset($parameters['TIME']));
        $this->assertTrue(isset($parameters['HMAC']));
    }

    public function testGetUrl()
    {
        $server = $this->_paybox->getUrl();

        $this->assertEquals('https://preprod-tpeweb.paybox.com/cgi-bin/ResAbon.cgi', $server);

        $server = $this->_paybox->getUrl('prod');

        $this->assertEquals('https://tpeweb.paybox.com/cgi-bin/ResAbon.cgi', $server);

        $this->setExpectedException('InvalidArgumentException', 'Invalid $env argument value.');
        $server = $this->_paybox->getUrl('bad');
    }

    protected function setUp()
    {

        $this->_paybox = new CancellationRequest(array(
            'site'  => 1999888,
            'rank'  => 32,
            'login' => 2,
            'hmac'  => array(
                'algorithm' => 'sha512',
                'key'       => '0123456789ABCDEF',
            ),
        ), array(
            'primary' => array(
                'protocol'    => 'https',
                'host'        => 'tpeweb.paybox.com',
                'system_path' => '/cgi/MYchoix_pagepaiement.cgi',
                'cancellation_path' => '/cgi-bin/ResAbon.cgi',
                'test_path'   => '/load.html',
            ),
            'secondary' => array(
                'protocol'    => 'https',
                'host'        => 'tpeweb1.paybox.com',
                'system_path' => '/cgi/MYchoix_pagepaiement.cgi',
                'cancellation_path' => '/cgi-bin/ResAbon.cgi',
                'test_path'   => '/load.html',
            ),
            'preprod' => array(
                'protocol'    => 'https',
                'host'        => 'preprod-tpeweb.paybox.com',
                'system_path' => '/cgi/MYchoix_pagepaiement.cgi',
                'cancellation_path' => '/cgi-bin/ResAbon.cgi',
                'test_path'   => '/load.html',
            ),
        ), new CurlTransport());

    //    $this->_paybox->setParameter('PBX_CMD',     'cmd123');
    }

    protected function tearDown()
    {
        $this->_paybox = null;
    }
}
