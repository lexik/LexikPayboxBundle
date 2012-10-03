<?php

namespace Lexik\Bundle\PayboxBundle\Tests\Service;

use Lexik\Bundle\PayboxBundle\Service\PayboxSystemParameterResolver;

/**
 * Paybox class tests.
 */
class PayboxSystemParameterResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers PayboxSystemParameterResolver::resolve
     * @covers PayboxSystemParameterResolver::initSimplePaiementParameters
     * @covers PayboxSystemParameterResolver::initParameters
     * @covers PayboxSystemParameterResolver::initAllowed
     */
    public function testResolveFirst()
    {
        $this->setExpectedException('InvalidArgumentException', 'The required options "PBX_CMD", "PBX_DEVISE", "PBX_HASH", "PBX_HMAC", "PBX_IDENTIFIANT", "PBX_PORTEUR", "PBX_RANG", "PBX_RETOUR", "PBX_SITE", "PBX_TIME", "PBX_TOTAL" are missing.');

        $resolver = new PayboxSystemParameterResolver();
        $resolver->resolve(array());
    }

    /**
     * @covers PayboxSystemParameterResolver::resolve
     * @covers PayboxSystemParameterResolver::initSimplePaiementParameters
     * @covers PayboxSystemParameterResolver::initParameters
     * @covers PayboxSystemParameterResolver::initAllowed
     */
    public function testResolveSecond()
    {
        $this->setExpectedException('InvalidArgumentException', 'The option "PBX_DEVISE" has the value "", but is expected to be one of "978", "950", "952", "953", "756", "826", "840", "124", "036", "959", "961", "962"');

        $resolver = new PayboxSystemParameterResolver();
        $resolver->resolve(array(
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
     * @covers PayboxSystemParameterResolver::resolve
     * @covers PayboxSystemParameterResolver::initSimplePaiementParameters
     * @covers PayboxSystemParameterResolver::initParameters
     * @covers PayboxSystemParameterResolver::initAllowed
     */
    public function testResolveThird()
    {
        $this->setExpectedException('InvalidArgumentException', 'The option "unknow" does not exist. Known options are: "PBX_1EURO_CODEEXTER", "PBX_1EURO_DATA", "PBX_2MONTn", "PBX_3DS", "PBX_ANNULE", "PBX_ARCHIVAGE", "PBX_AUTOSEULE", "PBX_CMD", "PBX_CODEFAMILLE", "PBX_CURRENCYDISPLAY", "PBX_DATEn", "PBX_DEVISE", "PBX_DIFF", "PBX_DISPLAY", "PBX_EFFECTUE", "PBX_EMPREINTE", "PBX_ENTITE", "PBX_ERRORCODETEST", "PBX_HASH", "PBX_HMAC", "PBX_IDABT", "PBX_IDENTIFIANT", "PBX_INTRUM_DATA", "PBX_LANGUE", "PBX_MAXICHEQUE_DATA", "PBX_NETRESERVE_DATA", "PBX_ONEY_DATA", "PBX_PAYPAL_DATA", "PBX_PORTEUR", "PBX_RANG", "PBX_REFABONNE", "PBX_REFUSE", "PBX_REPONDRE_A", "PBX_RETOUR", "PBX_RUF1", "PBX_SITE", "PBX_SOURCE", "PBX_TIME", "PBX_TOTAL", "PBX_TYPECARTE", "PBX_TYPEPAIEMENT"');

        $resolver = new PayboxSystemParameterResolver();
        $resolver->resolve(array(
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
