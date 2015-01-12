<?php

namespace Lexik\Bundle\PayboxBundle\Paybox;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AbstractParameterResolver
 *
 * @package Lexik\Bundle\PayboxBundle\Paybox
 */
abstract class AbstractParameterResolver implements ParameterResolverInterface
{
    /**
     * @var OptionsResolver
     */
    protected $resolver;

    /**
     * Constructor initialize all available parameters.
     */
    public function __construct()
    {
        $this->resolver = new OptionsResolver();
    }

    /**
     * Initialize the OptionResolver with required/optionnal options and allowed values.
     */
    abstract protected function initResolver();
}
