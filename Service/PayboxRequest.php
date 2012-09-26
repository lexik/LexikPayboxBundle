<?php

namespace Lexik\Bundle\PayboxBundle\Service;

use Symfony\Component\Form\FormFactory;
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
     * @param array       $parameters
     * @param array       $servers
     * @param FormFactory $factory
     */
    public function __construct(array $parameters, array $servers, FormFactory $factory)
    {
        parent::__construct($parameters);

        $this->servers = $servers;
        $this->factory = $factory;
    }

    /**
     * Returns a form with
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
            $doc->loadHTMLFile(sprintf(
                '%s://%s/load.html',
                $server['protocol'],
                $server['host']
            ));
            $element = $doc->getElementById('server_status');

            if ($element && 'OK' == $element->textContent) {
                return sprintf(
                    '%s://%s%s',
                    $server['protocol'],
                    $server['host'],
                    $server['path']
                );
            }
        }

        throw new RuntimeException('No server available.');
    }
}
