<?php

namespace Lexik\Bundle\PayboxBundle\Tests\Paybox\DirectPlus;

use Lexik\Bundle\PayboxBundle\Paybox\DirectPlus\ParameterResolver;

/**
 * Class ParameterResolverTest
 *
 * @package Lexik\Bundle\PayboxBundle\Tests\Paybox\DirectPlus
 */
class ParameterResolverTest extends \PHPUnit_Framework_TestCase
{
    public function testRequiredFields()
    {
        $this->setExpectedException('InvalidArgumentException', 'The required options "CLE", "DATEQ", "NUMQUESTION", "RANG", "SITE", "TYPE", "VERSION" are missing.');

        $resolver = new ParameterResolver(array());
        $resolver->resolve(array());
    }

    public function testParametersNormalization()
    {
        $parameters = array(
            'CLE'                 => '',
            'ACTIVITE'            => '024',
            'DATENAISS'           => 10,
            'DATEQ'               => 10,
            'DIFFERE'             => 10,
            'ERRORCODETEST'       => 10,
            'ID3D'                => 10,
            'MONTANT'             => 10,
            'NUMAPPEL'            => 10,
            'NUMQUESTION'         => 10,
            'NUMTRANS'            => 10,
            'PRIV_CODETRAITEMENT' => 10,
            'RANG'                => 10,
            'SITE'                => 10,
            'TYPE'                => '00051',
            'VERSION'             => '00104',
        );

        $expected = array(
            'CLE'                 => '',
            'ACTIVITE'            => '024',
            'DATENAISS'           => '00000010',
            'DATEQ'               => '00000000000010',
            'DIFFERE'             => '010',
            'ERRORCODETEST'       => '00010',
            'ID3D'                => '00000000000000000010',
            'MONTANT'             => '0000000010',
            'NUMAPPEL'            => '0000000010',
            'NUMQUESTION'         => '0000000010',
            'NUMTRANS'            => '0000000010',
            'PRIV_CODETRAITEMENT' => '010',
            'RANG'                => '010',
            'SITE'                => '0000010',
            'TYPE'                => '00051',
            'VERSION'             => '00104',
        );

        $resolver = new ParameterResolver(array('978'));
        $result = $resolver->resolve($parameters);

        $this->assertEquals($expected, $result);
    }

    public function testMinimumParameters()
    {
        $parameters = array(
            'CLE'                 => '',
            'DATEQ'               => '24122014145000',
            'NUMQUESTION'         => 10,
            'RANG'                => 10,
            'SITE'                => 10,
            'TYPE'                => '00051',
            'VERSION'             => '00104',
        );

        $expected = array(
            'CLE'                 => '',
            'DATEQ'               => '24122014145000',
            'NUMQUESTION'         => '0000000010',
            'RANG'                => '010',
            'SITE'                => '0000010',
            'TYPE'                => '00051',
            'VERSION'             => '00104',
        );

        $resolver = new ParameterResolver(array('978'));
        $result = $resolver->resolve($parameters);

        $this->assertEquals($expected, $result);
    }
}
