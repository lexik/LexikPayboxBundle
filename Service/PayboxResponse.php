<?php

namespace Lexik\Bundle\PayboxBundle\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Log\LoggerInterface;

class PayboxResponse
{
    private $request;

    private $logger;

    private $data;

    private $signature;

    /**
     *  Contructor.
     *
     * @param array           $parameters
     * @param Request         $request
     * @param LoggerInterface $logger
     */
    public function __construct(array $parameters, Request $request, LoggerInterface $logger)
    {
        $this->request = $request;
        $this->logger  = $logger;
    }

    protected function initSignature()
    {
        if ($this->request->request->has('Sign')) {
            $signature = $this->request->request->get('Sign');

            switch (strlen($signature)) {
                case 172 :
                    $this->signature = base64_decode($signature);
                    $this->logger->info(sprintf('Signature : "%s"', $this->signature));
                    break;

                case 128 :
                    $this->signature = $signature;
                    $this->logger->info(sprintf('Signature : "%s"', $this->signature));
                    break;

                default :
                    $this->logger->err(sprintf('Bad signature format : "%s"', $signature));
                    break;
            }
        } else {
            $this->logger->err('Payment signature not set.');
        }
    }

    protected function initData()
    {
        $this->logger->info('New IPN call.');
        $datas = array();

        foreach ($this->request->request as $key => $value) {
            $this->logger->info(sprintf('%s=%s', $key, $value));

            if ('Sign' !== $key) {
                $datas[] = sprintf('%s=%s', $key, $value);
            }
        }

        $this->data = implode('&', $datas);
    }

    public function verifySignature()
    {
        $this->initData();
        $this->initSignature();

        $file = fopen(dirname(__FILE__) . '/../Resources/config/pubkey.pem', 'r');
        $cert = fread($file, 8192);
        fclose($file);

        $pubkeyid = openssl_get_publickey($cert);

        $result = openssl_verify(
            $this->data,
            $this->signature,
            $pubkeyid
        );

        if ($result == 1) {
            $this->logger->info('Signature is valid.');
        } elseif ($result == 0) {
            $this->logger->err('Signature is invalid.');
        } else {
            $this->logger->err('Error while verifying Signature.');
        }

        openssl_free_key($pubkeyid);

        return (1 == $result);
    }
}
