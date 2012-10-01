<?php

namespace Lexik\Bundle\PayboxBundle\Service;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

use Lexik\Bundle\PayboxBundle\Service\Paybox;

class PayboxRequest extends Paybox
{
    /**
     * Array of servers informations.
     *
     * @var array
     */
    protected $servers;

    /**
     * FormFactory.
     *
     * @var FormFactory
     */
    protected $factory;

    /**
     * Constructor.
     *
     * @param array                $parameters
     * @param array                $servers
     * @param FormFactoryInterface $factory
     */
    public function __construct(array $parameters, array $servers, FormFactoryInterface $factory)
    {
        parent::__construct($parameters);

        $this->servers = $servers;
        $this->factory = $factory;
    }

    /**
     * Returns a form with defined parameters.
     *
     * @param  array $options
     * @return Form
     */
    public function getSimplePaymentForm($options = array())
    {
        $options['csrf_protection'] = false;

        $parameters = $this->getSimplePaymentParameters();
        $builder = $this->factory->createBuilder('form', $parameters, $options);

        foreach ($parameters as $key => $value) {
            $builder->add($key, 'hidden');
        }

        return $builder->getForm();
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
        if (!in_array($env, array('dev', 'prod'))) {
            throw new InvalidArgumentException('Invalid $env argument value.');
        }

        $servers = array();
        if ('dev' === $env) {
            $servers[] = $this->servers['preprod'];
        } else {
            $servers[] = $this->servers['primary'];
            $servers[] = $this->servers['secondary'];
        }

        foreach ($servers as $server) {
            $doc = new \DOMDocument();
            $doc->loadHTML($this->getWebPage(sprintf(
                '%s://%s%s',
                $server['protocol'],
                $server['host'],
                $server['test_path']
            )));
            $element = $doc->getElementById('server_status');

            if ($element && 'OK' == $element->textContent) {
                return sprintf(
                    '%s://%s%s',
                    $server['protocol'],
                    $server['host'],
                    $server['cgi_path']
                );
            }
        }

        throw new RuntimeException('No server available.');
    }

    /**
     * Returns the content of a web resource.
     *
     * @param  string $url
     * @return string
     */
    public function getWebPage($url)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL,            $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER,         false);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        $output = curl_exec($curl);
        curl_close($curl);

        return (string) $output;
    }
}
