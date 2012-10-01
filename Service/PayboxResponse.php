<?php

namespace Lexik\Bundle\PayboxBundle\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

use Lexik\Bundle\PayboxBundle\Service\Paybox;
use Lexik\Bundle\PayboxBundle\Event\PayboxResponseEvent;

class PayboxResponse extends Paybox
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var EventDispatcher
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
     *  Contructor.
     *
     * @param array           $parameters
     * @param Request         $request
     * @param LoggerInterface $logger
     * @param EventDispatcher $dispatcher
     */
    public function __construct(array $parameters, Request $request, LoggerInterface $logger, EventDispatcher $dispatcher)
    {
        parent::__construct($parameters);

        $this->request    = $request;
        $this->logger     = $logger;
        $this->dispatcher = $dispatcher;
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
     */
    protected function initSignature()
    {
        if ($this->getRequestParameters()->has('Sign')) {
            $signature = $this->getRequestParameters()->get('Sign');

            switch (strlen($signature)) {
                case 172 :
                    $this->signature = base64_decode($signature);
                    break;

                case 128 :
                    $this->signature = $signature;
                    break;

                default :
                    $this->logger->err(sprintf('Bad signature format.', $signature));
                    break;
            }
        } else {
            $this->logger->err('Payment signature not set.');
        }
    }

    /**
     * Concatenates all parameters set in the http request.
     */
    protected function initData()
    {
        foreach ($this->getRequestParameters() as $key => $value) {
            $this->logger->info(sprintf('%s=%s', $key, $value));

            if ('Sign' !== $key) {
                $this->data[$key] = $value;
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

        $file = fopen(dirname(__FILE__) . '/../Resources/config/paybox_public_key.pem', 'r');
        $cert = fread($file, 8192);
        fclose($file);

        $publicKey = openssl_get_publickey($cert);

        $result = openssl_verify(
            self::stringify($this->data),
            $this->signature,
            $publicKey
        );

        $this->logger->info(self::stringify($this->data));
        $this->logger->info(base64_encode($this->signature));

        if ($result == 1) {
            $this->logger->info('Signature is valid.');
        } elseif ($result == 0) {
            $this->logger->err('Signature is invalid.');
        } else {
            $this->logger->err('Error while verifying Signature.');
        }

        $result = (1 == $result);

        openssl_free_key($publicKey);

        $event = new PayboxResponseEvent($this->data, $result);
        $this->dispatcher->dispatch('paybox.ipn_response', $event);

        return $result;
    }
}
