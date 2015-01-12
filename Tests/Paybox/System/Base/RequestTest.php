<?php

namespace Lexik\Bundle\PayboxBundle\Tests\Paybox\System;

use Lexik\Bundle\PayboxBundle\Paybox\System\Base\Request;

/**
 * Class RequestTest
 *
 * @package Lexik\Bundle\PayboxBundle\Tests\Paybox\System
 *
 * @author Olivier Maisonneuve <o.maisonneuve@lexik.fr>
 */
class RequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Request
     */
    private $_paybox;

    public function testInitParameters()
    {
        $this->assertEquals(1999888, $this->_paybox->getParameter('pbx_site'));
        $this->assertEquals(32, $this->_paybox->getParameter('pbx_rang'));
        $this->assertEquals(2, $this->_paybox->getParameter('pbx_identifiant'));
        $this->assertEquals('sha512', $this->_paybox->getParameter('pbx_hash'));
    }

    public function testSetParameter()
    {
        $this->_paybox->setParameter('pbx_annule', 'Test');
        $this->_paybox->setParameter('PBX_ARCHIVAGE', 'Test');

        $this->assertEquals($this->_paybox->getParameter('PBX_ANNULE'), 'Test');
        $this->assertEquals($this->_paybox->getParameter('pbx_archivage'), 'Test');

        $this->assertNull($this->_paybox->getParameter('PBX_DISPLAY'));
    }

    public function testSetParameters()
    {
        $this->_paybox->setParameters(array(
            'pbx_annule'    => 'Test',
            'PBX_ARCHIVAGE' => 'Test',
        ));

        $this->assertEquals($this->_paybox->getParameter('PBX_ANNULE'), 'Test');
        $this->assertEquals($this->_paybox->getParameter('pbx_archivage'), 'Test');
    }

    public function testGetParameters()
    {
        $this->assertTrue(null === $this->_paybox->getParameter('PBX_TIME'));
        $this->assertTrue(null === $this->_paybox->getParameter('PBX_HMAC'));

        $parameters = $this->_paybox->getParameters();

        $this->assertTrue(isset($parameters['PBX_TIME']));
        $this->assertTrue(isset($parameters['PBX_HMAC']));

        $qs  = 'PBX_CMD=cmd123';
        $qs .= '&PBX_DEVISE=978';
        $qs .= '&PBX_HASH=sha512';
        $qs .= '&PBX_IDENTIFIANT=2';
        $qs .= '&PBX_PORTEUR=test@test.net';
        $qs .= '&PBX_RANG=32';
        $qs .= '&PBX_RETOUR=Mt:M;Ref:R;Auto:A;Erreur:E;Sign:K'; // "Sign:K" is automaticaly added at the end of PBX_RETOUR
        $qs .= '&PBX_SITE=1999888';
        $qs .= '&PBX_TIME=' . $parameters['PBX_TIME'];
        $qs .= '&PBX_TOTAL=100';

        $this->assertEquals(
            $parameters['PBX_HMAC'],
            strtoupper(hash_hmac(
                'sha512',
                $qs,
                pack('H*', '0123456789ABCDEF')
            ))
        );
    }

    public function testGetUrl()
    {
        $server = $this->_paybox->getUrl();

        $this->assertEquals('https://preprod-tpeweb.paybox.com/cgi/MYchoix_pagepaiement.cgi', $server);

        $reflection = new \ReflectionProperty(get_class($this->_paybox), 'globals');
        $reflection->setAccessible(true);
        $globals = $reflection->getValue($this->_paybox);
        $globals['production'] = true;
        $reflection->setValue($this->_paybox, $globals);

        $server = $this->_paybox->getUrl();

        $this->assertEquals('https://tpeweb.paybox.com/cgi/MYchoix_pagepaiement.cgi', $server);
    }

    protected function setUp()
    {
        $formFactory = $this->getMock('Symfony\Component\Form\FormFactoryInterface');

        $this->_paybox = new Request(array(
            'production' => false,
            'currencies' => array(
                '036', // AUD
                '124', // CAD
                '756', // CHF
                '826', // GBP
                '840', // USD
                '978', // EUR
            ),
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
            )
        ), $formFactory);

        $this->_paybox->setParameter('PBX_CMD',     'cmd123');
        $this->_paybox->setParameter('PBX_DEVISE',  '978');
        $this->_paybox->setParameter('PBX_PORTEUR', 'test@test.net');
        $this->_paybox->setParameter('PBX_RETOUR',  'Mt:M;Ref:R;Auto:A;Sign:K;Erreur:E');
        $this->_paybox->setParameter('PBX_TOTAL',   '100');
    }

    protected function tearDown()
    {
        $this->_paybox = null;
    }
}
