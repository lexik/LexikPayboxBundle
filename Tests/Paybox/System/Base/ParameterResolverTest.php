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
        $this->setExpectedException('InvalidArgumentException');

        $resolver = new ParameterResolver(array());
        $resolver->resolve(array());
    }

    public function testResolveNoCurrency()
    {
        $this->setExpectedException('InvalidArgumentException');

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
        $this->setExpectedExceptionRegExp('InvalidArgumentException');

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
        $this->setExpectedException('InvalidArgumentException');

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
