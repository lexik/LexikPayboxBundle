<?php

namespace Lexik\Bundle\PayboxBundle\Paybox\DirectPlus;

use Lexik\Bundle\PayboxBundle\Paybox\AbstractParameterResolver;

/**
 * Class ParameterResolver
 * Based on PAYBOX DIRECT PLUS VERSION 6.2 documentation.
 *
 * @package Lexik\Bundle\PayboxBundle\Paybox\DirectPlus
 *
 * @author Lexik <dev@lexik.fr>
 * @author Olivier Maisonneuve <o.maisonneuve@lexik.fr>
 */
class ParameterResolver extends AbstractParameterResolver
{
    /**
     * @var array All availables parameters DirectPlus calls.
     */
    private $knownParameters = array(
        'VERSION'             => '%05d',
        'TYPE'                => '%05d',
        'SITE'                => '%07d',
        'RANG'                => '%03d',
        'CLE'                 => '%s',
        'NUMQUESTION'         => '%010d',
        'DATEQ'               => '%014s',
        'ACQUEREUR'           => null,
        'ACTIVITE'            => '%03d',
        'ARCHIVAGE'           => null,
        'AUTORISATION'        => null,
        'CODEREPONSE'         => null,
        'COMMENTAIRE'         => null,
        'CVV'                 => null,
        'DATENAISS'           => '%08d',
        'DATEVAL'             => null,
        'DEVISE'              => null,
        'DIFFERE'             => '%03d',
        'ERRORCODETEST'       => '%05d',
        'ID3D'                => null,
        'MONTANT'             => '%010d',
        'NUMAPPEL'            => '%010d',
        'NUMTRANS'            => '%010d',
        'PAYS'                => null,
        'PORTEUR'             => null,
        'PRIV_CODETRAITEMENT' => '%03d',
        'REFABONNE'           => null,
        'REFERENCE'           => null,
        'REMISE'              => null,
        'SHA-1'               => null,
        'STATUS'              => null,
        'TYPECARTE'           => null,
    );

    /**
     * @var array Requireds parameters for any DirectPlus call.
     */
    private $requiredParameters = array(
        'VERSION',
        'TYPE',
        'SITE',
        'RANG',
        'CLE',
        'NUMQUESTION',
        'DATEQ',
    );

    /**
     * @var array
     */
    private $currencies;

    /**
     * Constructor initialize all available parameters.
     *
     * @param array $currencies
     */
    public function __construct(array $currencies)
    {
        parent::__construct();

        $this->currencies = $currencies;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(array $parameters)
    {
        $this->initResolver();

        $result = $this->resolver->resolve($parameters);
        $result = $this->normalize($result);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    protected function initResolver()
    {
        $this->resolver->setRequired($this->requiredParameters);

        $this->resolver->setDefined(array_diff(array_keys($this->knownParameters), $this->requiredParameters));

        $this->initAllowed();
    }

    /**
     * Initialize allowed values.
     */
    protected function initAllowed()
    {
        $this->resolver
            ->setAllowedValues('ACTIVITE', array(
                '020',
                '021',
                '022',
                '023',
                '024',
                '027',
                20,
                21,
                22,
                23,
                24,
                27,
            ))
            ->setAllowedValues('DEVISE', $this->currencies)
            ->setAllowedValues('TYPE', array(
                '00001',
                '00002',
                '00003',
                '00004',
                '00005',
                '00011',
                '00012',
                '00013',
                '00014',
                '00017',
                '00051',
                '00052',
                '00053',
                '00054',
                '00055',
                '00056',
                '00057',
                '00058',
                '00061',
                1,
                2,
                3,
                4,
                5,
                11,
                12,
                13,
                14,
                17,
                51,
                52,
                53,
                54,
                55,
                56,
                57,
                58,
                61,
            ))
            ->setAllowedValues('VERSION', array(
                '00103',
                '00104',
                103,
                104,
            ))
        ;
    }

    /**
     * Normalizes parameters depending on Paybox's 6.2 parameters specifications.
     *
     * @param array $parameters
     *
     * @return array
     */
    protected function normalize(array $parameters)
    {
        foreach ($parameters as $parameter => $value) {
            if (null !== $this->knownParameters[$parameter]) {
                $parameters[$parameter] = sprintf($this->knownParameters[$parameter], $value);
            }
        }

        return $parameters;
    }
}
