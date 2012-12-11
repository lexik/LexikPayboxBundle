<?php

namespace Lexik\Bundle\PayboxBundle\Paybox\System;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\Options;

/**
 * Paybox\System\CancellationParameterResolver class.
 *
 * @author Olivier Maisonneuve <fabien.pomerol@gmail.com>
 */
class CancellationParameterResolver
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
     * Constructor initialize all available parameters.
     */
    public function __construct()
    {
        $this->resolver = new OptionsResolver();

        $this->knownParameters = array(
            'VERSION',
            'TYPE',
            'SITE',
            'IDENTIFIANT',
            'MACH',
            'HMAC',
            'TIME',
            'ABONNEMENT',
            'REFERENCE',
        );
    }

    /**
     * Resolves parameters for a cancellation call.
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
     * Initialise required parameters for a cancellation call.
     */
    protected function initParameters()
    {
        $this->requiredParameters = array(
            'VERSION',
            'TYPE',
            'SITE',
            'IDENTIFIANT',
            'MACH',
            'HMAC',
            'TIME',
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
     * Initialize allowed values for the cancellation OptionResolver.
     */
    protected function initAllowed()
    {
        $this->resolver->setAllowedValues(array(
            'VERSION' => array('001'),
            'TYPE'    => array('001'),
        ));
    }
}
