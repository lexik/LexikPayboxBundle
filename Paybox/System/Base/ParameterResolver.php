<?php

namespace Lexik\Bundle\PayboxBundle\Paybox\System\Base;

use Lexik\Bundle\PayboxBundle\Paybox\AbstractParameterResolver;

/**
 * Class ParameterResolver
 *
 * @package Lexik\Bundle\PayboxBundle\Paybox\System\Base
 *
 * @author Lexik <dev@lexik.fr>
 * @author Olivier Maisonneuve <o.maisonneuve@lexik.fr>
 */
class ParameterResolver extends AbstractParameterResolver
{
    /**
     * @var array All availables parameters for payments requests.
     */
    private $knownParameters = array(
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

    /**
     * @var array Requireds parameters for a standard payment request.
     */
    private $requiredParameters = array(
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

        return $this->resolver->resolve($parameters);
    }

    /**
     * {@inheritdoc}
     */
    protected function initResolver()
    {
        $this->resolver->setRequired($this->requiredParameters);

        $this->resolver->setDefined(array_diff($this->knownParameters, $this->requiredParameters));

        $this->initAllowed();
    }

    /**
     * Initialize allowed values.
     *
     * @see http://www.fastwrite.com/resources/core/iso-currency-codes/index.php
     */
    protected function initAllowed()
    {
        $this->resolver
            ->setAllowedValues('PBX_DEVISE', $this->currencies)
            ->setAllowedValues('PBX_RUF1', array(
                'GET',
                'POST',
            ))
            ->setAllowedValues('PBX_TYPECARTE', array(
                'CB',
                'VISA',
                'EUROCARD_MASTERCARD',
                'E_CARD',
                'MAESTRO',
                'AMEX',
                'DINERS',
                'JCB',
                'COFINOGA',
                'SOFINCO',
                'AURORE',
                'CDGP',
                '24H00',
                'RIVEGAUCHE',
                'BCMC',
                'PAYPAL',
                'UNEURO',
                '34ONEY',
                'NETCDGP',
                'SVS',
                'KADEOS',
                'PSC',
                'CSHTKT',
                'LASER',
                'EMONEO',
                'IDEAL',
                'ONEYKDO',
                'ILLICADO',
                'MAXICHEQUE',
                'KANGOUROU',
                'FNAC',
                'CYRILLUS',
                'PRINTEMPS',
                'CONFORAMA',
                'LEETCHI',
                'PAYBUTTING'
            ))
        ;
    }
}
