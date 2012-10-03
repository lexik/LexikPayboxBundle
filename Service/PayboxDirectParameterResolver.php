<?php

namespace Lexik\Bundle\PayboxBundle\Service;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\Options;

/**
 *
 */
class PayboxDirectParameterResolver
{
    const PAYBOX_DIRECT_VERSION = '001';

    private $knownParameters;

    private $requiredParameters;

    private $resolver;

    /**
     * Constructor initialise all availables parameters.
     */
    public function __construct()
    {
        $this->resolver = new OptionsResolver();

        $this->knownParameters = array(
            'ABONNEMENT',
            'DEVISE',
            'HMAC',
            'IDENTIFIANT',
            'MACH',
            'MONTANT',
            'NUMQUESTION',
            'PORTEUR',
            'REFERENCE',
            'SITE',
            'TIME',
            'TYPE',
            'VERSION',
        );
    }

    /**
     * Resolves parameters for a paiement call.
     *
     * @param  array $parameters
     * @return Options
     */
    public function resolve(array $parameters)
    {
        $this->initParameters();

        return $this->resolver->resolve($parameters);
    }

    /**
     * Initialise required options for a paiement call.
     */
    protected function initParameters()
    {
        $this->requiredParameters = array(
            'VERSION',
            'TYPE',
            'SITE',
            'MACH',
            'IDENTIFIANT',
            'HMAC',
            'TIME',
        );

        $this->initResolver();
    }

    /**
     * Initialise the OptionResolver with required/optionnal options and allowed values.
     */
    protected function initResolver()
    {
        $this->resolver->setRequired($this->requiredParameters);

        $this->resolver->setOptional(array_diff($this->knownParameters, $this->requiredParameters));

        $this->initAllowed();
    }

    /**
     * Initialise allowed values.
     */
    protected function initAllowed()
    {
        $this->resolver->setAllowedValues(array(
            'DEVISE' => array(
                '978', // EUR
                '950', // XAF
                '952', // XOF
                '953', // XPF
                '756', // CHF
                '826', // GBP
                '840', // USD
                '124', // CAD
                '036', // AUD
                '959', // XAU
                '961', // XAG
                '962', // XPT
            ),
            'TYPE' => array(
                '001', // Autorisation
                '002', // Débit
                '003', // Autorisation + Débit
                '004', // Crédit
                '005', // Annulation
                '011', // Vérification de l'existence d'une transaction
                '012', // Transaction sans demande d'autorisation
                '013', // Modification du montant d'une transaction
                '014', // Remboursement
                '017', // Consultation
                '051', // Autorisation seule sur un abonné
                '052', // Débit sur un abonné
                '053', // Autorisation + Débit sur un abonné
                '054', // Crédit sur un abonné
                '055', // Annulation d'une opération sur un abonné
                '056', // Inscription nouvel abonné
                '057', // Modification abonné existant
                '058', // Suppression abonné
                '061', // Transaction sans demande d'autorisation (forçage)
            ),
            'VERSION' => array(
                '001',
            ),
        ));
    }
}
