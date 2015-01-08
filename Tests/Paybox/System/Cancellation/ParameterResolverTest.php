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
        $this->setExpectedException('InvalidArgumentException', 'The required options "HMAC", "IDENTIFIANT", "MACH", "SITE", "TIME", "TYPE", "VERSION" are missing.');

        $resolver = new ParameterResolver();
        $resolver->resolve(array());
    }

    public function testResolveSecond()
    {
        $this->setExpectedException('InvalidArgumentException', 'The option "VERSION" with value "" is invalid. Accepted values are: "001".');

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
        $this->setExpectedException('InvalidArgumentException', 'The option "unknow" does not exist. Known options are: "ABONNEMENT", "HMAC", "IDENTIFIANT", "MACH", "REFERENCE", "SITE", "TIME", "TYPE", "VERSION"');

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
