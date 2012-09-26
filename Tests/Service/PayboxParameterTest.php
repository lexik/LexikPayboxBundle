<?php

namespace Lexik\Bundle\PayboxBundle\Tests\Service;

use Lexik\Bundle\PayboxBundle\Service\PayboxParameter;

/**
 * Paybox class tests.
 */
class PayboxParameterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers PayboxParameter::resolveSimplePaiement
     * @covers PayboxParameter::initSimplePaiementParameters
     * @covers PayboxParameter::initParameters
     * @covers PayboxParameter::initAllowed
     */
    public function testResolveSimplePaiementFirst()
    {
        $this->setExpectedException('InvalidArgumentException', 'The required options "PBX_CMD", "PBX_DEVISE", "PBX_HASH", "PBX_HMAC", "PBX_IDENTIFIANT", "PBX_PORTEUR", "PBX_RANG", "PBX_RETOUR", "PBX_SITE", "PBX_TIME", "PBX_TOTAL" are missing.');

        $resolver = new PayboxParameter();
        $resolver->resolveSimplePaiement(array());
    }

    /**
     * @covers PayboxParameter::resolveSimplePaiement
     * @covers PayboxParameter::initSimplePaiementParameters
     * @covers PayboxParameter::initParameters
     * @covers PayboxParameter::initAllowed
     */
    public function testResolveSimplePaiementSecond()
    {
        $this->setExpectedException('InvalidArgumentException', 'The option "PBX_DEVISE" has the value "", but is expected to be one of "756", "978", "826", "036", "124", "840", "952"');

        $resolver = new PayboxParameter();
        $resolver->resolveSimplePaiement(array(
            'PBX_CMD' => '',
            'PBX_DEVISE' => '',
            'PBX_HASH' => '',
            'PBX_HMAC' => '',
            'PBX_IDENTIFIANT' => '',
            'PBX_PORTEUR' => '',
            'PBX_RANG' => '',
            'PBX_RETOUR' => '',
            'PBX_SITE' => '',
            'PBX_TIME' => '',
            'PBX_TOTAL' => '',
        ));
    }

    /**
     * @covers PayboxParameter::resolveSimplePaiement
     * @covers PayboxParameter::initSimplePaiementParameters
     * @covers PayboxParameter::initParameters
     * @covers PayboxParameter::initAllowed
     */
    public function testResolveSimplePaiementThird()
    {
        $this->setExpectedException('InvalidArgumentException', 'The option "unknow" does not exist. Known options are: "PBX_CMD", "PBX_DEVISE", "PBX_HASH", "PBX_HMAC", "PBX_IDENTIFIANT", "PBX_PORTEUR", "PBX_RANG", "PBX_RETOUR", "PBX_SITE", "PBX_TIME", "PBX_TOTAL"');

        $resolver = new PayboxParameter();
        $resolver->resolveSimplePaiement(array(
            'unknow' => '',
            'PBX_CMD' => '',
            'PBX_DEVISE' => '978',
            'PBX_HASH' => '',
            'PBX_HMAC' => '',
            'PBX_IDENTIFIANT' => '',
            'PBX_PORTEUR' => '',
            'PBX_RANG' => '',
            'PBX_RETOUR' => '',
            'PBX_SITE' => '',
            'PBX_TIME' => '',
            'PBX_TOTAL' => '',
        ));
    }
}
