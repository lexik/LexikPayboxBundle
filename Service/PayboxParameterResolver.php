<?php

namespace Lexik\Bundle\PayboxBundle\Service;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\Options;

class PayboxParameterResolver
{
    private $knownParameters;

    private $requiredParameters;

    private $resolver;

    /**
     * Constructor initialise all available parameters.
     */
    public function __construct()
    {
        $this->resolver = new OptionsResolver();

        $this->knownParameters = array(
            'PBX_1EURO_CODEEXTER',
            'PBX_1EURO_DATA',
            'PBX_2MONTn',
            'PBX_3DS',
            'PBX_ANNULE',
            'PBX_ARCHIVAGE',
            'PBX_AUTOSEULE',
            'PBX_CMD',
            'PBX_CODEFAMILLE',
            'PBX_CURRENCYDISPLAY',
            'PBX_DATEn',
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
        );
    }

    /**
     * Resolves parameters for a simple paiement call.
     *
     * @param  array $parameters
     * @return Options
     */
    public function resolveSimplePaiement(array $parameters)
    {
        $this->initSimplePaiementParameters();

        return $this->resolver->resolve($parameters);
    }

    /**
     * Initialise required options for a simple paiement call.
     */
    protected function initSimplePaiementParameters()
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

        $this->initParameters();
    }

    /**
     * Initialise the OptionResolver with required/optionnal options and allowed values.
     */
    protected function initParameters()
    {
        $this->resolver->setRequired($this->requiredParameters);

        $this->resolver->setOptional(array_diff($this->requiredParameters, $this->knownParameters));

        $this->initAllowed();
    }

    /**
     * Initialise allowed values.
     */
    protected function initAllowed()
    {
        $this->resolver->setAllowedValues(array(
            'PBX_DEVISE' => array(
                '756', // CHF
                '978', // EUR
                '826', // GBP
                '036', // AUD
                '124', // CAD
                '840', // USD
                '952', // XOF
            ),
        ));
    }
}
