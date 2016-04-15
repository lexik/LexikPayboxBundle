<?php

namespace Lexik\Bundle\PayboxBundle\Paybox\System\Base;

use Lexik\Bundle\PayboxBundle\Event\PayboxEvents;
use Lexik\Bundle\PayboxBundle\Event\PayboxResponseEvent;
use Lexik\Bundle\PayboxBundle\Paybox\System\Tools;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request as HttpRequest;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class Response
 *
 * @package Lexik\Bundle\PayboxBundle\Paybox\System\Base
 *
 * @author Lexik <dev@lexik.fr>
 * @author Olivier Maisonneuve <o.maisonneuve@lexik.fr>
 */
class Response
{
    /**
     * @var HttpRequest
     */
    private $request;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var array
     */
    private $data;

    /**
     * @var string
     */
    private $signature;

    /**
     * @var array
     */
    private $parameters;

    /**
     * Contructor.
     *
     * @param RequestStack             $requestStack
     * @param LoggerInterface          $logger
     * @param EventDispatcherInterface $dispatcher
     * @param array                    $parameters
     */
    public function __construct(RequestStack $requestStack, LoggerInterface $logger, EventDispatcherInterface $dispatcher, array $parameters)
    {
        $this->request    = $requestStack->getCurrentRequest();
        $this->logger     = $logger;
        $this->dispatcher = $dispatcher;
        $this->parameters = $parameters;
    }

    /**
     * Returns the GET or POST parameters form the request.
     *
     * @return ParameterBag
     */
    protected function getRequestParameters()
    {
        if ($this->request->isMethod('POST')) {
            $parameters = $this->request->request;
        } else {
            $parameters = $this->request->query;
        }

        return $parameters;
    }

    /**
     * Gets the signature set in the http request.
     *
     * Paybox documentation says :
     *     The Paybox signature is created by encrypting a SHA-1 hash with the private Paybox RSA key. The size
     *     of a SHA-1 hash is 160 bits and the size of the Paybox key is 1024 bits. The signature is always a binary
     *     value of fixed 128 bytes size (172 bytes in Base64 encoding).
     *
     * But sometimes, base64 encoded signature are also url encoded.
     */
    protected function initSignature()
    {
        if (!$this->getRequestParameters()->has($this->parameters['hmac']['signature_name'])) {
            $this->logger->error('Payment signature not set.');

            return false;
        }

        $signature = $this->getRequestParameters()->get($this->parameters['hmac']['signature_name']);
        $signatureLength = strlen($signature);

        if ($signatureLength > 172) {
            $this->signature = base64_decode(urldecode($signature));

            return true;
        } elseif ($signatureLength == 172) {
            $this->signature = base64_decode($signature);

            return true;
        } elseif ($signatureLength == 128) {
            $this->signature = $signature;

            return true;
        } else {
            $this->signature = null;
            $this->logger->error('Bad signature format.');

            return false;
        }
    }

    /**
     * Concatenates all parameters set in the http request.
     */
    protected function initData()
    {
        foreach ($this->getRequestParameters() as $key => $value) {
            $this->logger->info(sprintf('%s=%s', $key, $value));

            if ($this->parameters['hmac']['signature_name'] !== $key) {
                $this->data[$key] = urlencode($value);
            }
        }
    }

    /**
     * Verifies the validity of the signature.
     *
     * @return bool
     */
    public function verifySignature()
    {
        $this->logger->info('New IPN call.');

        $this->initData();
        $this->initSignature();

        $file = fopen($this->parameters['public_key'], 'r');
        $cert = fread($file, 1024);
        fclose($file);

        $publicKey = openssl_pkey_get_public($cert);

        $result = openssl_verify(
            Tools::stringify($this->data),
            $this->signature,
            $publicKey,
            'sha1WithRSAEncryption'
        );

        $this->logger->info(Tools::stringify($this->data));
        $this->logger->info(base64_encode($this->signature));

        if ($result == 1) {
            $this->logger->info('Signature is valid.');
        } elseif ($result == 0) {
            $this->logger->error('Signature is invalid.');
        } else {
            $this->logger->error('Error while verifying Signature.');
        }

        $result = (1 == $result);

        openssl_free_key($publicKey);

        $event = new PayboxResponseEvent($this->data, $result);
        $this->dispatcher->dispatch(PayboxEvents::PAYBOX_IPN_RESPONSE, $event);

        return $result;
    }
}
