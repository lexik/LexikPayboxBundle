<?php

namespace Lexik\Bundle\PayboxBundle\Paybox\RemoteMpi;

use Lexik\Bundle\PayboxBundle\Paybox\AbstractParameterResolver;

class ParameterResolver extends AbstractParameterResolver
{
    /**
     * @var array All availables parameters Remote MPI calls.
     */
    private $knownParameters = array(
        'Amount'                => '%010d',
        'CCExpDate'             => '%04d',
        'CCNumber'              => '%16d',
        'Currency'              => '%03d',
        'CVVCode'               => '%3d',
        'IdMerchant'            => '%d',
        'IdSession'             => null,
        'URLHttpDirect'         => null,
        'URLRetour'             => null,
        '3DCAVV'                => '%028d',
        '3DCAVVALGO'            => '%064s',
        '3DECI'                 => '%02d',
        '3DENROLLED'            => '%01s',
        '3DERROR'               => '%06s',
        '3DSIGNVAL'             => '%01d',
        '3DSTATUS'              => '%01s',
        '3DXID'                 => '%025s',
        'Check'                 => '%0256s',
        'ID3D'                  => '%20d',
        'StatusPBX'             => '%s',
    );

    /**
     * @var array Requireds parameters for any DirectPlus call.
     */
    private $requiredParameters = array(
        'Amount',
        'CCExpDate',
        'CCNumber',
        'Currency',
        'CVVCode',
        'IdMerchant',
        'IdSession',
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
