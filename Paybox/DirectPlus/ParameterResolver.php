<?php

namespace Lexik\Bundle\PayboxBundle\Paybox\DirectPlus;

use Symfony\Component\OptionsResolver\OptionsResolver;
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
class ParameterResolver
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
     * @var OptionsResolver
     */
    private $resolver;

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
        $this->resolver = new OptionsResolver();

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
            ),
            'VERSION' => array(
                '00103',
                '00104',
                103,
                104,
            ),
        ));
    }

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
