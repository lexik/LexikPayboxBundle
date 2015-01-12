<?php

namespace Lexik\Bundle\PayboxBundle\Tests\Transport;

use Lexik\Bundle\PayboxBundle\Paybox\RequestInterface;
use Lexik\Bundle\PayboxBundle\Paybox\System\Cancellation\Request;
use Lexik\Bundle\PayboxBundle\Transport\CurlTransport;

/**
 * Test class for CurlTransport
 */
class CurlTransportTest extends \PHPUnit_Framework_TestCase
{
    protected $object;

    protected $globals;

    protected $server;

    public function setUp()
    {
        if (!function_exists('curl_init')) {
            $this->markTestSkipped('cURL is not available. Activate it first.');
        }

        $this->object = new CurlTransport();

        $this->server = array('system' => array('protocol' => 'http', 'host' => 'test.com', 'cancellation_path' => 'test.cgi'));
        $this->globals = array('currencies' => array(), 'site' => '052', 'rank' => '032', 'login' => '12345679', 'hmac' => array('key' => '123123133', 'algorithm' => 'sha512', 'signature_name' => 'Sign'));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testCall()
    {
        $this->object->setEndpoint('http://www.google.com/hey.cgi');
        $method = new \ReflectionMethod('\Lexik\Bundle\PayboxBundle\Transport\CurlTransport', 'call');

        $cancellationRequest = new Request($this->globals, $this->server, $this->object);
        $cancellationRequest->setParameter('HMAC', 'test');
        $cancellationRequest->setParameter('TIME', 'test');

        $response = $method->invoke($this->object, $cancellationRequest);

        $this->assertTrue(is_string($response));
    }

    public function testCallEmpty()
    {
        $curl = new mockCurlTransport();

        $this->assertEquals($curl->call(new Request($this->globals, $this->server, $this->object)), '');
    }

}

class mockCurlTransport extends CurlTransport
{
    public function call(RequestInterface $request)
    {
        return '';
    }
}
