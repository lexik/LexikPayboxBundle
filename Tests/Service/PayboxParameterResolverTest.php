<?php

namespace Lexik\Bundle\PayboxBundle\Tests\Service;

use Lexik\Bundle\PayboxBundle\Service\PayboxParameterResolver;

/**
 * Paybox class tests.
 */
class PayboxParameterResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers PayboxParameterResolver::resolveSimplePaiement
     * @covers PayboxParameterResolver::initSimplePaiementParameters
     * @covers PayboxParameterResolver::initParameters
     * @covers PayboxParameterResolver::initAllowed
     */
    public function testResolveSimplePaiementFirst()
    {
        $this->setExpectedException('InvalidArgumentException', 'The required options "PBX_CMD", "PBX_DEVISE", "PBX_HASH", "PBX_HMAC", "PBX_IDENTIFIANT", "PBX_PORTEUR", "PBX_RANG", "PBX_RETOUR", "PBX_SITE", "PBX_TIME", "PBX_TOTAL" are missing.');

        $resolver = new PayboxParameterResolver();
        $resolver->resolveSimplePaiement(array());
    }

    /**
     * @covers PayboxParameterResolver::resolveSimplePaiement
     * @covers PayboxParameterResolver::initSimplePaiementParameters
     * @covers PayboxParameterResolver::initParameters
     * @covers PayboxParameterResolver::initAllowed
     */
    public function testResolveSimplePaiementSecond()
    {
        $this->setExpectedException('InvalidArgumentException', 'The option "PBX_DEVISE" has the value "", but is expected to be one of "756", "978", "826", "036", "124", "840", "952"');

        $resolver = new PayboxParameterResolver();
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
     * @covers PayboxParameterResolver::resolveSimplePaiement
     * @covers PayboxParameterResolver::initSimplePaiementParameters
     * @covers PayboxParameterResolver::initParameters
     * @covers PayboxParameterResolver::initAllowed
     */
    public function testResolveSimplePaiementThird()
    {
        $this->setExpectedException('InvalidArgumentException', 'The option "unknow" does not exist. Known options are: "PBX_CMD", "PBX_DEVISE", "PBX_HASH", "PBX_HMAC", "PBX_IDENTIFIANT", "PBX_PORTEUR", "PBX_RANG", "PBX_RETOUR", "PBX_SITE", "PBX_TIME", "PBX_TOTAL"');

        $resolver = new PayboxParameterResolver();
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
