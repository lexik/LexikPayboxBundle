<?php

namespace Lexik\Bundle\PayboxBundle\Paybox\System;

use Symfony\Component\Form\FormFactoryInterface;

use Lexik\Bundle\PayboxBundle\Paybox\Paybox;
use Lexik\Bundle\PayboxBundle\Paybox\System\CancellationParameterResolver;
use Lexik\Bundle\PayboxBundle\Transport\TransportInterface;

/**
 * Paybox\System\CancellationRequest class.
 *
 * @author Fabien Pomerol <fabien.pomerol@gmail.com>
 */
class CancellationRequest extends Paybox
{
    /**
     * @var TransportInterface This is how
     * you will point to Paybox (via cURL or Shell)
     */
    protected $transport;

    /**
     * Constructor.
     *
     * @param array                $parameters
     * @param array                $servers
     * @param FormFactoryInterface $factory
     */
    public function __construct(array $parameters, array $servers, TransportInterface $transport = null)
    {
        $this->transport = $transport;
        parent::__construct($parameters, $servers);
    }

    /**
     * @see Paybox::initParameters()
     */
    protected function initParameters()
    {
        $this->setParameter('VERSION', '001');
        $this->setParameter('TYPE', '001');
        $this->setParameter('SITE', $this->globals['site']);
        $this->setParameter('MACH', $this->formatRankParameter($this->globals['rank']));
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

        $resolver = new CancellationParameterResolver();

        return $resolver->resolve($this->parameters);
    }

    /**
     * Returns the url of an available server.
     *
     * @param  string $env
     *
     * @return string
     */
    public function getUrl($env = 'dev')
    {
        $server = $this->getServer($env);

        return sprintf(
            '%s://%s%s',
            $server['protocol'],
            $server['host'],
            $server['cancellation_path']
        );
    }

    /**
     * {@inheritDoc}
     *
     * @param PayboxRequest $request Request instance
     *
     * @throws RuntimeException On cURL error
     *
     * @return string $response The html of the temporary form
     */
    public function cancel($reference = null, $subscriptionId = null)
    {
        if ($reference) {
          $this->setParameter('REFERENCE', $reference);
        }
        if ($subscriptionId) {
          $this->setParameter('ABONNEMENT', $reference);
        }

        $this->transport->setEndpoint($this->getUrl());

        return $this->transport->call($this);
    }

    /**
     * Return a well formed rank paramaters on 3 chars
     * For a cancellation request the rank parameter is required on 3 chars
     * instead of 2 for a payment request.
     *
     * @param the rank param
     *
     * @return the rank param
     */
    public function formatRankParameter($rank)
    {
        if (strlen($rank) < 3) {
            return '0'.$rank;
        }

        return $rank;
    }
}
