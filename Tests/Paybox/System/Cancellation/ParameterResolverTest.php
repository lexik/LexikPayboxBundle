<?php

namespace Lexik\Bundle\PayboxBundle\Tests\Paybox\System\Cancellation;

use Lexik\Bundle\PayboxBundle\Paybox\System\Cancellation\ParameterResolver;
use PHPUnit\Framework\TestCase;

/**
 * Paybox\System\ParameterResolver class tests.
 *
 * @author Fabien Pomerol <fabien.pomerol@gmail.com>
 */
class ParameterResolverTest extends TestCase
{
    public function testResolveFirst()
    {
        $this->expectException(\InvalidArgumentException::class);

        $resolver = new ParameterResolver();
        $resolver->resolve(array());
    }

    public function testResolveSecond()
    {
        $this->expectException(\InvalidArgumentException::class);

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
        $this->expectException(\InvalidArgumentException::class);

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
