<?php

namespace Lexik\Bundle\PayboxBundle\Paybox\DirectPlus;

use Buzz\Browser;
use Buzz\Client\Curl;
use Lexik\Bundle\PayboxBundle\Event\PayboxEvents;
use Lexik\Bundle\PayboxBundle\Event\PayboxResponseEvent;
use Lexik\Bundle\PayboxBundle\Paybox\AbstractPaybox;
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
class Manager extends AbstractPaybox
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
     * Constructor.
     *
     * @param array                    $parameters
     * @param array                    $servers
     * @param LoggerInterface          $logger
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(array $parameters, array $servers, LoggerInterface $logger, EventDispatcherInterface $dispatcher)
    {
        $this->parameters = array();
        $this->globals    = array();
        $this->servers    = $servers['direct_plus'];
        $this->logger     = $logger;
        $this->dispatcher = $dispatcher;

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
            'production' => $parameters['production'],
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
        $this->setParameter('VERSION', null); // 'VERSION' must be the first parameter so it have to be declared in first...
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
     * @return AbstractPaybox
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
    public function callApi()
    {
        $browser = new Browser(new Curl());
        $response = $browser->submit($this->getUrl(), $this->getParameters());
        $request = $browser->getLastRequest();

        $this->logger->info('New API call.');
        $this->logger->info('Url : ' . $this->getUrl());
        $this->logger->info('Request content : ' . $request->getContent());
        $this->logger->info('Response content : ' . $response->getContent());

        if ($response->isOk()) {
            parse_str($response->getContent(), $result);

            $verified = ('00000' === $result['CODEREPONSE']) && !empty($result['NUMTRANS']) && !empty($result['NUMAPPEL']);

            $event = new PayboxResponseEvent($result, $verified);
            $this->dispatcher->dispatch(PayboxEvents::PAYBOX_API_RESPONSE, $event);

            return $result;
        } else {
            $this->logger->error('Http error.');

            return false;
        }
    }

    /**
     * Returns the url of the server.
     *
     * @return string
     */
    protected function getUrl()
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
