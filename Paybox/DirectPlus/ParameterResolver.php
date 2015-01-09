<?php

namespace Lexik\Bundle\PayboxBundle\Paybox\DirectPlus;

use Lexik\Bundle\PayboxBundle\Paybox\AbstractParameterResolver;
use Symfony\Component\OptionsResolver\Options;

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
        'DATEQ'               => '%014d',
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
        'ID3D'                => '%020d',
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
     * Resolves parameters for a payment call.
     *
     * @param  array $parameters
     *
     * @return Options
     */
    public function resolve(array $parameters)
    {
        $this->initResolver();

        return $this->resolver->resolve($parameters);
    }

    /**
     * Initialize the OptionResolver with required/optionnal options and allowed values.
     */
    protected function initResolver()
    {
        $this->resolver->setRequired($this->requiredParameters);

        $this->resolver->setDefined(array_diff(array_keys($this->knownParameters), $this->requiredParameters));

        $this->initAllowed();

        $this->initNormalizers();
    }

    /**
     * Initialize allowed values.
     */
    protected function initAllowed()
    {
        $this->resolver->setAllowedValues(array(
            'ACTIVITE' => array(
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
            ),
            'DEVISE' => $this->currencies,
            'TYPE'   => array(
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
            ),
            'VERSION' => array(
                '00103',
                '00104',
                103,
                104,
            ),
        ));
    }

    /**
     * Initialization of basic normalizers for parameters.
     * Depending on Paybox's 6.2 parameters specifications.
     */
    protected function initNormalizers()
    {
        foreach ($this->knownParameters as $parameter => $pattern) {
            if (null !== $pattern) {
                $this->resolver->setNormalizer($parameter,  function ($options, $value) use ($pattern) {
                    return sprintf($pattern, $value);
                });
            }
        }
    }
}
