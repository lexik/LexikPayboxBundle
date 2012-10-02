<?php

namespace Lexik\Bundle\PayboxBundle\Service;

use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

use Lexik\Bundle\PayboxBundle\Service\Paybox;
use Lexik\Bundle\PayboxBundle\Service\PayboxDirectParameterResolver;

/**
 *
 */
class PayboxDirectManager extends Paybox
{
    /**
     * @see Paybox::initParameters()
     */
    protected function initParameters()
    {
        $this->setParameter('VERSION', '001');
        $this->setParameter('SITE', $this->globals['site']);
        $this->setParameter('MACH', $this->globals['rank']);
        $this->setParameter('IDENTIFIANT', $this->globals['login']);
    }

    /**
     * Returns all parameters set for a payment.
     *
     * @return array
     */
    public function getParameters()
    {
        if (null === $this->getParameter('HMAC')) {
            $this->setParameter('TIME', date('c'));
            $this->setParameter('HMAC', strtoupper(parent::computeHmac()));
        }

        $resolver = new PayboxDirectParameterResolver();
        return $resolver->resolve($this->parameters);
    }

    /**
     * Execute a request to Paybox direct plus.
     *
     * @param string $env
     * @return array
     */
    public function doRequest($env='dev')
    {
        $curl = curl_init($this->getUrl($env));
        curl_setopt($curl, CURLOPT_HEADER,         false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST,           true);
        curl_setopt($curl, CURLOPT_POSTFIELDS,     http_build_query($this->getParameters()));
        $output = curl_exec($curl);
        curl_close($curl);

        $values = array();
        foreach (explode('&', $output) as $parameter) {
            list($key, $value) = explode('=', $parameter);
            $values[$key] = (string) $value;
        }

        return $values;
    }

    /**
     * Returns the url of an available server.
     *
     * @param  string $env
     * @return string
     *
     * @throws InvalidArgumentException If the specified environment is not valid (dev/prod).
     * @throws RuntimeException         If no server is available.
     */
    public function getUrl($env = 'dev')
    {
        $server = $this->getServer($env);

        return sprintf(
            '%s://%s%s',
            $server['protocol'],
            $server['host'],
            $server['direct_path']
        );
    }
}
