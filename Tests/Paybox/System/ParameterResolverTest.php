<?php

namespace Lexik\Bundle\PayboxBundle\Tests\Paybox\System;

use Lexik\Bundle\PayboxBundle\Paybox\System\Base\ParameterResolver;

/**
 * Paybox\System\ParameterResolver class tests.
 *
 * @author Olivier Maisonneuve <o.maisonneuve@lexik.fr>
 */
class ParameterResolverTest extends \PHPUnit_Framework_TestCase
{
    public function testResolveFirst()
    {
        $this->setExpectedException('InvalidArgumentException', 'The required options "PBX_CMD", "PBX_DEVISE", "PBX_HASH", "PBX_HMAC", "PBX_IDENTIFIANT", "PBX_PORTEUR", "PBX_RANG", "PBX_RETOUR", "PBX_SITE", "PBX_TIME", "PBX_TOTAL" are missing.');

        $resolver = new ParameterResolver(array());
        $resolver->resolve(array());
    }

    public function testResolveNoCurrency()
    {
        $this->setExpectedException('InvalidArgumentException', 'The option "PBX_DEVISE" has the value "", but is expected to be one of "953"');

        $resolver = new ParameterResolver(array('953'));
        $resolver->resolve(array(
            'PBX_CMD'         => '',
            'PBX_DEVISE'      => '',
            'PBX_HASH'        => '',
            'PBX_HMAC'        => '',
            'PBX_IDENTIFIANT' => '',
            'PBX_PORTEUR'     => '',
            'PBX_RANG'        => '',
            'PBX_RETOUR'      => '',
            'PBX_SITE'        => '',
            'PBX_TIME'        => '',
            'PBX_TOTAL'       => '',
        ));
    }

    public function testResolveBadCurrency()
    {
        $this->setExpectedException('InvalidArgumentException', 'The option "PBX_DEVISE" has the value "978", but is expected to be one of "953"');

        $resolver = new ParameterResolver(array('953'));
        $resolver->resolve(array(
            'PBX_CMD'         => '',
            'PBX_DEVISE'      => '978',
            'PBX_HASH'        => '',
            'PBX_HMAC'        => '',
            'PBX_IDENTIFIANT' => '',
            'PBX_PORTEUR'     => '',
            'PBX_RANG'        => '',
            'PBX_RETOUR'      => '',
            'PBX_SITE'        => '',
            'PBX_TIME'        => '',
            'PBX_TOTAL'       => '',
        ));
    }

    public function testResolveUndefinedParameter()
    {
        $this->setExpectedException('InvalidArgumentException', 'The option "unknow" does not exist. Known options are: "PBX_1EURO_CODEEXTER", "PBX_1EURO_DATA", "PBX_2MONT1", "PBX_2MONT2", "PBX_2MONT3", "PBX_3DS", "PBX_ANNULE", "PBX_ARCHIVAGE", "PBX_ATTENTE", "PBX_AUTOSEULE", "PBX_CMD", "PBX_CODEFAMILLE", "PBX_CURRENCYDISPLAY", "PBX_DATE1", "PBX_DATE2", "PBX_DATE3", "PBX_DEVISE", "PBX_DIFF", "PBX_DISPLAY", "PBX_EFFECTUE", "PBX_EMPREINTE", "PBX_ENTITE", "PBX_ERRORCODETEST", "PBX_HASH", "PBX_HMAC", "PBX_IDABT", "PBX_IDENTIFIANT", "PBX_INTRUM_DATA", "PBX_LANGUE", "PBX_MAXICHEQUE_DATA", "PBX_NETRESERVE_DATA", "PBX_ONEY_DATA", "PBX_PAYPAL_DATA", "PBX_PORTEUR", "PBX_RANG", "PBX_REFABONNE", "PBX_REFUSE", "PBX_REPONDRE_A", "PBX_RETOUR", "PBX_RUF1", "PBX_SITE", "PBX_SOURCE", "PBX_TIME", "PBX_TOTAL", "PBX_TYPECARTE", "PBX_TYPEPAIEMENT"');

        $resolver = new ParameterResolver(array());
        $resolver->resolve(array(
            'unknow'          => '',
            'PBX_CMD'         => '',
            'PBX_DEVISE'      => '',
            'PBX_HASH'        => '',
            'PBX_HMAC'        => '',
            'PBX_IDENTIFIANT' => '',
            'PBX_PORTEUR'     => '',
            'PBX_RANG'        => '',
            'PBX_RETOUR'      => '',
            'PBX_SITE'        => '',
            'PBX_TIME'        => '',
            'PBX_TOTAL'       => '',
        ));
    }
}
