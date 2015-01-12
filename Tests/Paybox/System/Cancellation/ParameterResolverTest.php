<?php

namespace Lexik\Bundle\PayboxBundle\Tests\Paybox\System\Cancellation;

use Lexik\Bundle\PayboxBundle\Paybox\System\Cancellation\ParameterResolver;

/**
 * Paybox\System\ParameterResolver class tests.
 *
 * @author Fabien Pomerol <fabien.pomerol@gmail.com>
 */
class ParameterResolverTest extends \PHPUnit_Framework_TestCase
{
    public function testResolveFirst()
    {
        $this->setExpectedException('InvalidArgumentException');

        $resolver = new ParameterResolver();
        $resolver->resolve(array());
    }

    public function testResolveSecond()
    {
        $this->setExpectedException('InvalidArgumentException');

        $resolver = new ParameterResolver();
        $resolver->resolve(array(
            'VERSION'     => '',
            'TYPE'        => '',
            'SITE'        => '',
            'IDENTIFIANT' => '',
            'MACH'        => '',
            'HMAC'        => '',
            'TIME'        => '',
        ));
    }

    public function testResolveThird()
    {
        $this->setExpectedException('InvalidArgumentException');

        $resolver = new ParameterResolver();
        $resolver->resolve(array(
            'unknow'      => '',
            'VERSION'     => '',
            'TYPE'        => '',
            'SITE'        => '',
            'IDENTIFIANT' => '',
            'MACH'        => '',
            'HMAC'        => '',
            'TIME'        => '',
        ));
    }
}
