<?php

namespace Lexik\Bundle\PayboxBundle\Transport;

use Lexik\Bundle\PayboxBundle\Paybox\System\Cancellation\Request;

/**
 * Transport\CurlTransport class.
 *
 * @author Fabien Pomerol <fabien.pomerol@gmail.com>
 */
class CurlTransport extends AbstractTransport implements TransportInterface
{
    /**
     * Constructor
     *
     * @param  string           $endpoint to paybox endpoint
     * @throws RuntimeException If cURL is not available
     */
    public function __construct($url = '')
    {
        if (!function_exists('curl_init')) {
            throw new \RuntimeException('cURL is not available. Activate it first.');
        }

        parent::__construct($url);
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
    public function call(Request $request)
    {
        $this->checkEndpoint();

        $ch = curl_init();

        // cURL options
        $options = array(
                CURLOPT_URL => $this->getEndpoint(),
                CURLOPT_HEADER => false,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => http_build_query($request->getParameters())
        );
        curl_setopt_array($ch, $options);

        $response = curl_exec($ch);

        $curlErrorNumber = curl_errno($ch);
        $curlErrorMessage = curl_error($ch);
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (!in_array($responseCode, array(0, 200, 201, 204))) {
            throw new \RuntimeException('cUrl returns some errors (cURL errno '.$curlErrorNumber.'): '.$curlErrorMessage.' (HTTP Code: '.$responseCode.')');
        }

        curl_close($ch);

        return $response;
    }

}
