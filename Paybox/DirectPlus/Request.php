<?php

namespace Lexik\Bundle\PayboxBundle\Paybox\DirectPlus;

use Lexik\Bundle\PayboxBundle\Event\PayboxEvents;
use Lexik\Bundle\PayboxBundle\Event\PayboxResponseEvent;
use Lexik\Bundle\PayboxBundle\Paybox\AbstractRequest;
use Lexik\Bundle\PayboxBundle\Transport\TransportInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class Request
 *
 * @package Lexik\Bundle\PayboxBundle\Paybox\DirectPlus
 *
 * @author Lexik <dev@lexik.fr>
 * @author Olivier Maisonneuve <o.maisonneuve@lexik.fr>
 */
class Request extends AbstractRequest
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
     * @var TransportInterface
     */
    private $transport;

    /**
     * Constructor.
     *
     * @param array                    $parameters
     * @param array                    $servers
     * @param LoggerInterface          $logger
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(array $parameters, array $servers, LoggerInterface $logger, EventDispatcherInterface $dispatcher, TransportInterface $transport)
    {
        $this->parameters = array();
        $this->globals    = array();
        $this->servers    = $servers['direct_plus'];
        $this->logger     = $logger;
        $this->dispatcher = $dispatcher;
        $this->transport  = $transport;

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
            'currencies' => $parameters['currencies'],
            'site'       => $parameters['site'],
            'rang'       => $parameters['rang'],
            'cle'        => $parameters['cle'],
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function initParameters()
    {
        $this->setParameter('VERSION', null); // 'VERSION' must be the first parameter so it have to be declared first...
        $this->setParameter('SITE',    $this->globals['site']);
        $this->setParameter('RANG',    $this->globals['rang']);
        $this->setParameter('CLE',     $this->globals['cle']);
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
        $this->parameters[strtoupper($name)] = $value;

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
     * @return array|false
     */
    public function send()
    {
        $this->logger->info('New API call.');
        $this->logger->info('Url : ' . $this->getUrl());

        $this->transport->setEndpoint($this->getUrl());
        $result = $this->transport->call($this);

        $this->logger->info('Data :');
        foreach ($this->getParameters() as $parameterName => $parameterValue) {
            /**
             * We don't want credit card's information in the server's log.
             */
            if (!in_array($parameterName, array('PORTEUR', 'DATEVAL', 'CVV'))) {
                $this->logger->info(sprintf(' > %s = %s', $parameterName, $parameterValue));
            }
        }

        $this->logger->info('Result : ' . $result);

        if (null === $result) {
            $this->logger->error('Http error.');

            return false;
        } else {
            parse_str($result, $result);

            if (isset($result['CODEREPONSE']) && isset($result['NUMTRANS']) && isset($result['NUMAPPEL'])) {
                $verified = ('00000' === $result['CODEREPONSE']) && !empty($result['NUMTRANS']) && !empty($result['NUMAPPEL']);

                $event = new PayboxResponseEvent($result, $verified);
                $this->dispatcher->dispatch(PayboxEvents::PAYBOX_API_RESPONSE, $event);

                return $result;
            } else {
                $this->logger->error('Bad response content.');

                return false;
            }
        }
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
            $this->servers[$server_name]['api_path']
        );
    }
}
