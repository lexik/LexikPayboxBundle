<?php

namespace Lexik\Bundle\PayboxBundle\Paybox\RemoteMpi;

use Lexik\Bundle\PayboxBundle\Event\PayboxEvents;
use Lexik\Bundle\PayboxBundle\Event\PayboxResponseEvent;
use Lexik\Bundle\PayboxBundle\Paybox\AbstractRequest;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use SquareCo\TransactionBundle\Services\ParameterResolver;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;

/**
 *  Remote Mpi Request
 *
 *  @author Romain Marecat <romain.marecat@gmail.com>
 *
 */
class RemoteMpiRequest extends AbstractRequest
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var FormFactoryInterface
     */
    protected $factory;

    /**
     * Constructor.
     *
     * @param array                    $parameters
     * @param array                    $servers
     * @param LoggerInterface          $logger
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(array $parameters, array $servers, LoggerInterface $logger, EventDispatcherInterface $dispatcher, FormFactoryInterface $factory)
    {
        $this->parameters = array();
        $this->globals    = array();
        $this->servers    = $servers['remote_mpi'];
        $this->logger     = $logger;
        $this->dispatcher = $dispatcher;
        $this->factory    = $factory;

        $this->initGlobals($parameters);
        $this->initParameters();
    }

    /**
     * Initialize the object with the defaults values.
     *
     * @param array $parameters
     */
    protected function initGlobals(array $parameters)
    {
        $this->globals = array(
            'production' => isset($parameters['production']) ? $parameters['production'] : false,
            'idmerchant' => $parameters['idmerchant'],
            'currencies' => $parameters['currencies'],
            'return_path' => $parameters['return_path'],
            'redirect_path' => $parameters['redirect_path'],
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function initParameters()
    {
        $this->setParameter('IdMerchant', $this->globals['idmerchant']);
        $this->setParameter('URLRetour', $this->globals['return_path']);
        $this->setParameter('URLHttpDirect', $this->globals['redirect_path']);
    }

    /**
     * Sets a parameter.
     *
     * @param  string $name
     * @param  mixed  $value
     *
     * @return Request
     */
    public function setParameter($name, $value)
    {
        $this->parameters[$name] = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters()
    {
        $resolver = new ParameterResolver($this->globals['currencies']);

        return $resolver->resolve($this->parameters);
    }

    /**
     * Returns a form with defined parameters.
     *
     * @param  array $options
     *
     * @return Form
     */
    public function getForm($options = array())
    {
        $options['csrf_protection'] = false;
        $options['action'] = $this->getUrl();

        $parameters = $this->getParameters();
        $builder = $this->factory->createNamedBuilder('', 'form', $parameters, $options);

        foreach ($parameters as $key => $value) {
            $builder->add($key, 'hidden');
        }
        $builder->add('submit', 'submit');

        return $builder->getForm();
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl()
    {
        $server_name = $this->globals['production'] ? 'primary' : 'preprod';

        return sprintf(
            '%s://%s%s',
            $this->servers[$server_name]['protocol'],
            $this->servers[$server_name]['host'],
            $this->servers[$server_name]['mpi_path']
        );
    }
}
