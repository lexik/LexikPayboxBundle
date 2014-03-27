<?php

namespace Lexik\Bundle\PayboxBundle\Paybox\System\Base;

use Symfony\Component\HttpFoundation\Request as HttpRequest;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Lexik\Bundle\PayboxBundle\Paybox\Paybox;
use Lexik\Bundle\PayboxBundle\Event\PayboxEvents;
use Lexik\Bundle\PayboxBundle\Event\PayboxResponseEvent;

/**
 * Paybox\System\Response class.
 *
 * @author Olivier Maisonneuve <o.maisonneuve@lexik.fr>
 */
class Response
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
     * @var string
     */
    private $publicKey;

    /**
     * @var string
     */
    private $validationBy;

    /**
     * @var array
     */
    private $pbxRetour;

    /**
     * Contructor.
     *
     * @param HttpRequest              $request
     * @param LoggerInterface          $logger
     * @param EventDispatcherInterface $dispatcher
     * @param string                   $publicKey
     * @param string                   $validationBy
     * @param array                    $pbxRetour
     */
    public function __construct(HttpRequest $request, LoggerInterface $logger, EventDispatcherInterface $dispatcher, $publicKey, $validationBy, array $pbxRetour)
    {
        $this->request    = $request;
        $this->logger     = $logger;
        $this->dispatcher = $dispatcher;
        $this->publicKey  = $publicKey;
        $this->validationBy = $validationBy;
        $this->pbxRetour  = $pbxRetour;
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
        if ($this->getRequestParameters()->has(Paybox::SIGNATURE_PARAMETER)) {
            $signature = $this->getRequestParameters()->get(Paybox::SIGNATURE_PARAMETER);

            switch (strlen($signature)) {
                case 172 :
                    $this->signature = base64_decode($signature);
                    break;

                case 128 :
                    $this->signature = $signature;
                    break;

                default :
                    $this->logger->err('Bad signature format.');
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

            if (in_array($key, $this->pbxRetour)) {
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

        $cert = file_get_contents($this->publicKey);
        $publicKey = openssl_get_publickey($cert);

        $result = openssl_verify(
            Paybox::stringify($this->data),
            $this->signature,
            $publicKey
        );

        $this->logger->info(Paybox::stringify($this->data));
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
        $this->dispatcher->dispatch(PayboxEvents::PAYBOX_IPN_RESPONSE, $event);

        return $result;
    }

}
