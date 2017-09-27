<?php

namespace Lexik\Bundle\PayboxBundle\Tests\Paybox\RemoteMpi;

use Lexik\Bundle\PayboxBundle\Paybox\RemoteMpi\Request;

/**
 * Class RequestTest
 *
 * @package Lexik\Bundle\PayboxBundle\Tests\Paybox\RemoteMpi
 *
 *  @author Romain Marecat <romain.marecat@gmail.com>
 */
class RequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Request
     */
    protected $request;

    protected $logger;

    protected $formFactory;

    protected $parameters;

    protected $servers;

    protected function setUp()
    {
        $this->parameters = array(
            'site'       => '1999888',
            'rang'       => '063',
            'cle'        => '1999888I',
            'idmerchant' => '109518543',
            'production' => false,
            'return_path'=> 'https://github.com/lexik/LexikPayboxBundle',
            'redirect_path' => 'https://github.com/lexik/LexikPayboxBundle',
            'currencies' => array(
                '036',
                '124',
                '756',
                '826',
                '840',
                '978',
            ),
        );

        $this->servers = array(
            'remote_mpi' => array(
                'primary' => array(
                    'protocol' => 'https',
                    'host'     => 'tpeweb.paybox.com',
                    'mpi_path' => '/cgi/RemoteMPI.cgi',
                ),
                'secondary' => array(
                    'protocol' => 'https',
                    'host'     => 'tpeweb1.paybox.com',
                    'mpi_path' => '/cgi/RemoteMPI.cgi',
                ),
                'preprod' => array(
                    'protocol' => 'https',
                    'host'     => 'preprod-tpeweb.paybox.com',
                    'mpi_path' => '/cgi/RemoteMPI.cgi',
                ),
            ),
        );

        $this->logger = $this->getMock('Psr\Log\LoggerInterface');
        $this->formFactory = $this->getMockBuilder('Symfony\Component\Form\FormFactoryInterface')->disableOriginalConstructor()->getMock();
        $this->request = new Request($this->parameters, $this->servers, $this->logger, $this->formFactory);
    }

    public function testGetForm()
    {
        $parameters = array(
            'Amount'                => '0000000100',
            'CCExpDate'             => '0117',
            'CCNumber'              => '1111222233334444',
            'Currency'              => '978',
            'CVVCode'               => '123',
            'IdSession'             => 'ORDER' . rand(1000, 9999),
            'IdMerchant'            => '109518543',
            'URLHttpDirect'         => 'https://github.com/lexik/LexikPayboxBundle',
            'URLRetour'             => 'https://github.com/lexik/LexikPayboxBundle',
        );

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')->disableOriginalConstructor()->getMock();

        $formBuilderInterface  = $this->getMockBuilder('Symfony\Component\Form\FormBuilderInterface')->disableOriginalConstructor()->getMock();
        $formBuilderInterface->expects($this->any())
         ->method('add')
         ->will($this->returnSelf());

        $formBuilderInterface->expects($this->any())->method('getForm')->will($this->returnValue($form));

        $this->formFactory = $this->getMockBuilder('Symfony\Component\Form\FormFactoryInterface')->disableOriginalConstructor()->getMock();
        // $this->formFactory->expects(
        //     $this->once()
        // )
        // ->method('createNamedBuilder')
        // ->with(
        //     '',
        //     'form',
        //     $parameters,
        //     array('csrf_protection' => false, 'action' => 'https://preprod-tpeweb.paybox.com/cgi/RemoteMPI.cgi')
        // );

        // $this->request = new Request($this->parameters, $this->servers, $this->logger, $this->formFactory);
        // $this->request->setParameters($parameters);

        // $view = $this->request->getForm();

    }

    public function testGetUrl()
    {
        $server = $this->request->getUrl();

        $this->assertEquals('https://preprod-tpeweb.paybox.com/cgi/RemoteMPI.cgi', $server);

        $reflection = new \ReflectionProperty(get_class($this->request), 'globals');
        $reflection->setAccessible(true);
        $globals = $reflection->getValue($this->request);
        $globals['production'] = true;
        $reflection->setValue($this->request, $globals);

        $server = $this->request->getUrl();

        $this->assertEquals('https://tpeweb.paybox.com/cgi/RemoteMPI.cgi', $server);
    }

    public function tearDown()
    {
        $this->request = null;
    }
}
