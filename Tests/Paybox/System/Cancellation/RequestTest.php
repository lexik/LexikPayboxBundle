<?php

namespace Lexik\Bundle\PayboxBundle\Tests\Paybox\System\Cancellation;

use Lexik\Bundle\PayboxBundle\Paybox\System\Cancellation\Request;
use Lexik\Bundle\PayboxBundle\Transport\CurlTransport;

/**
 * Paybox\System\Cancellation\Request class tests.
 *
 * @author Fabien Pomerol <fabien.pomerol@gmail.com>
 */
class RequestTest extends \PHPUnit_Framework_TestCase
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

        $reflection = new \ReflectionProperty(get_class($this->_paybox), 'globals');
        $reflection->setAccessible(true);
        $globals = $reflection->getValue($this->_paybox);
        $globals['production'] = true;
        $reflection->setValue($this->_paybox, $globals);

        $server = $this->_paybox->getUrl();

        $this->assertEquals('https://tpeweb.paybox.com/cgi-bin/ResAbon.cgi', $server);
    }

    protected function setUp()
    {
        $this->_paybox = new Request(array(
            'production' => false,
            'currencies' => array(),
            'site'       => 1999888,
            'rank'       => 32,
            'login'      => 2,
            'hmac'       => array(
                'algorithm'      => 'sha512',
                'key'            => '0123456789ABCDEF',
                'signature_name' => 'Sign',
            ),
        ), array(
            'system' => array(
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
            )
        ), new CurlTransport());
    }

    protected function tearDown()
    {
        $this->_paybox = null;
    }
}
