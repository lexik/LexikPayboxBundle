<?php

namespace Lexik\Bundle\PayboxBundle\Paybox\System\Base;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\Options;

/**
 * Paybox\System\ParameterResolver class.
 *
 * @author Olivier Maisonneuve <o.maisonneuve@lexik.fr>
 */
class ParameterResolver
{
    /**
     * @var array
     */
    private $knownParameters;

    /**
     * @var array
     */
    private $requiredParameters;

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
        $this->knownParameters = array(
            'PBX_1EURO_CODEEXTER',
            'PBX_1EURO_DATA',
            'PBX_2MONT1',
            'PBX_2MONT2',
            'PBX_2MONT3',
            'PBX_3DS',
            'PBX_ANNULE',
            'PBX_ARCHIVAGE',
            'PBX_AUTOSEULE',
            'PBX_CMD',
            'PBX_CODEFAMILLE',
            'PBX_CURRENCYDISPLAY',
            'PBX_DATE1',
            'PBX_DATE2',
            'PBX_DATE3',
            'PBX_DEVISE',
            'PBX_DIFF',
            'PBX_DISPLAY',
            'PBX_EFFECTUE',
            'PBX_EMPREINTE',
            'PBX_ENTITE',
            'PBX_ERRORCODETEST',
            'PBX_HASH',
            'PBX_HMAC',
            'PBX_IDABT',
            'PBX_IDENTIFIANT',
            'PBX_INTRUM_DATA',
            'PBX_LANGUE',
            'PBX_MAXICHEQUE_DATA',
            'PBX_NETRESERVE_DATA',
            'PBX_ONEY_DATA',
            'PBX_PAYPAL_DATA',
            'PBX_PORTEUR',
            'PBX_RANG',
            'PBX_REFABONNE',
            'PBX_REFUSE',
            'PBX_REPONDRE_A',
            'PBX_RETOUR',
            'PBX_RUF1',
            'PBX_SITE',
            'PBX_SOURCE',
            'PBX_TIME',
            'PBX_TOTAL',
            'PBX_TYPECARTE',
            'PBX_TYPEPAIEMENT',
            'PBX_ATTENTE',
        );
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
        $this->initParameters();

        return $this->resolver->resolve($parameters);
    }

    /**
     * Initialise required parameters for a payment call.
     */
    protected function initParameters()
    {
        $this->requiredParameters = array(
            'PBX_SITE',
            'PBX_RANG',
            'PBX_IDENTIFIANT',
            'PBX_TOTAL',
            'PBX_DEVISE',
            'PBX_CMD',
            'PBX_PORTEUR',
            'PBX_RETOUR',
            'PBX_HASH',
            'PBX_TIME',
            'PBX_HMAC',
        );

        $this->initResolver();
    }

    /**
     * Initialize the OptionResolver with required/optionnal options and allowed values.
     */
    protected function initResolver()
    {
        $this->resolver->setRequired($this->requiredParameters);

        $this->resolver->setOptional(array_diff($this->knownParameters, $this->requiredParameters));

        $this->initAllowed();
    }

    /**
     * Initialize allowed values.
     * @see http://www.fastwrite.com/resources/core/iso-currency-codes/index.php
     */
    protected function initAllowed()
    {
        $this->resolver->setAllowedValues(array(
            'PBX_DEVISE' => $this->currencies,
            'PBX_RUF1'   => array(
                'GET',
                'POST',
            ),
            'PBX_TYPECARTE' => array(
                'CB',
                'VISA',
                'EUROCARD_MASTERCARD',
            )
        ));
    }
}
