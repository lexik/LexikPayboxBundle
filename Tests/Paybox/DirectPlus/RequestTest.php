<?php

namespace Lexik\Bundle\PayboxBundle\Tests\Paybox\DirectPlus;

use Lexik\Bundle\PayboxBundle\Paybox\DirectPlus\Request;
use Lexik\Bundle\PayboxBundle\Transport\BuzzTransport;
use Lexik\Bundle\PayboxBundle\Transport\CurlTransport;

/**
 * Class RequestTest
 *
 * @package Lexik\Bundle\PayboxBundle\Tests\Paybox\DirectPlus
 *
 * @author Olivier Maisonneuve <o.maisonneuve@lexik.fr>
 */
class RequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @param array  $messages
     * @param string $httpResponse
     * @param bool   $dispatch
     */
    protected function initMock(array $messages, $httpResponse = null, $dispatch = false)
    {
        $parameters = array(
            'site'       => '1999888',
            'rang'       => '032',
            'cle'        => '1999888I',
            'production' => false,
            'currencies' => array(
                '036',
                '124',
                '756',
                '826',
                '840',
                '978',
            ),
        );

        $servers = array(
            'direct_plus' => array(
                'primary' => array(
                    'protocol' => 'https',
                    'host'     => 'ppps.paybox.com',
                    'api_path' => '/PPPS.php',
                ),
                'secondary' => array(
                    'protocol' => 'https',
                    'host'     => 'ppps1.paybox.com',
                    'api_path' => '/PPPS.php',
                ),
                'preprod' => array(
                    'protocol' => 'https',
                    'host'     => 'preprod-ppps.paybox.com',
                    'api_path' => '/PPPS.php',
                ),
            ),
        );

        $logger = $this->getMock('Psr\Log\LoggerInterface');
        foreach ($messages as $i => $message) {
            $logger
                ->expects($this->at($i))
                ->method($message[0])
                ->with(new \PHPUnit_Framework_Constraint_StringMatches($message[1]))
            ;
        }

        $dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcher');
        if (true === $dispatch) {
            $dispatcher
                ->expects($this->once())
                ->method('dispatch')
            ;
        }

        $transport = $this->getMockForAbstractClass('Lexik\Bundle\PayboxBundle\Transport\AbstractTransport');
        if (null !== $httpResponse) {
            $transport
                ->expects($this->once())
                ->method('call')
                ->will($this->returnValue($httpResponse))
            ;
        }

        /**
         * @wtf Shut... You haven't seen the lines below, ok ?
         */
        // $transport = new CurlTransport();
        // $transport = new BuzzTransport();

        $this->request = new Request($parameters, $servers, $logger, $dispatcher, $transport);
    }

    public function tearDown()
    {
        $this->request = null;
    }

    public function testGetParametersAddsAccountInformations()
    {
        $this->initMock(array());

        $parameters = array(
            'VERSION'     => '00104',
            'TYPE'        => '00056',
            'NUMQUESTION' => '194102422',
            'MONTANT'     => '1000',
            'DEVISE'      => '978',
            'REFERENCE'   => 'TestPaybox',
            'porteur'     => '1111222233334444',
            'DATEVAL'     => '0520',
            'cvv'         => '222',
            'REFABONNE'   => 'ABODOCUMENTATION001',
            'ACTIVITE'    => '027',
            'DATEQ'       => '30012013',
        );

        $this->request->setParameters($parameters);
        $result = $this->request->getParameters();

        $expected = array(
            'SITE'        => '1999888',
            'DATEQ'       => '30012013',
            'RANG'        => '032',
            'VERSION'     => '00104',
            'TYPE'        => '00056',
            'NUMQUESTION' => '194102422',
            'MONTANT'     => '1000',
            'CLE'         => '1999888I',
            'DEVISE'      => '978',
            'REFABONNE'   => 'ABODOCUMENTATION001',
            'REFERENCE'   => 'TestPaybox',
            'DATEVAL'     => '0520',
            'PORTEUR'     => '1111222233334444',
            'CVV'         => '222',
            'ACTIVITE'    => '027',
        );

        $this->assertEquals($expected, $result);
    }

    public function testParameterNamesAreForcedToUpperCase()
    {
        $this->initMock(array());

        $parameters = array(
            'cle'                 => '',
            'activite'            => '024',
            'datenaiss'           => '00000010',
            'dateq'               => '00000000000010',
            'differe'             => '010',
            'errorcodetest'       => '00010',
            'id3d'                => '00000000000000000010',
            'montant'             => '0000000010',
            'numappel'            => '0000000010',
            'numquestion'         => '0000000010',
            'numtrans'            => '0000000010',
            'priv_codetraitement' => '010',
            'rang'                => '010',
            'site'                => '0000010',
            'type'                => '00051',
            'version'             => '00104',
        );

        $expected = array(
            'CLE'                 => '',
            'ACTIVITE'            => '024',
            'DATENAISS'           => '00000010',
            'DATEQ'               => '00000000000010',
            'DIFFERE'             => '010',
            'ERRORCODETEST'       => '00010',
            'ID3D'                => '00000000000000000010',
            'MONTANT'             => '0000000010',
            'NUMAPPEL'            => '0000000010',
            'NUMQUESTION'         => '0000000010',
            'NUMTRANS'            => '0000000010',
            'PRIV_CODETRAITEMENT' => '010',
            'RANG'                => '010',
            'SITE'                => '0000010',
            'TYPE'                => '00051',
            'VERSION'             => '00104',
        );

        $this->request->setParameters($parameters);
        $result = $this->request->getParameters();

        $this->assertEquals($expected, $result);
    }

    public function testVersionParameterMustBeFirst()
    {
        $this->initMock(array());

        $parameters = array(
            'CLE'         => '',
            'DATEQ'       => '24122014145000',
            'NUMQUESTION' => 10,
            'RANG'        => 10,
            'SITE'        => 10,
            'TYPE'        => '00051',
            'VERSION'     => 104,
        );

        $this->request->setParameters($parameters);
        $result = $this->request->getParameters();

        $keys = array_keys($result);

        $this->assertEquals('VERSION', $keys[0]);
        $this->assertEquals('00104', $result['VERSION']);
    }

    public function testSendSimpleAutorization()
    {
        $time = time();

        $this->initMock(array(
            array('info', 'New API call.'),
            array('info', 'Url : https://preprod-ppps.paybox.com/PPPS.php'),
            array('info', 'Data :'),
            array('info', ' > VERSION = 00104'),
            array('info', ' > SITE = 1999888'),
            array('info', ' > RANG = 032'),
            array('info', ' > CLE = 1999888I'),
            array('info', ' > TYPE = 00001'),
            array('info', ' > NUMQUESTION = ' . sprintf('%010d', $time)),
            array('info', ' > MONTANT = 0000001000'),
            array('info', ' > DEVISE = 978'),
            array('info', ' > REFERENCE = TestPaybox'),
            array('info', ' > ACTIVITE = 024'),
            array('info', ' > DATEQ = ' . sprintf('%014s', $time)),
            array('info', 'Result : NUMTRANS=%s&NUMAPPEL=%s&NUMQUESTION='.sprintf('%010d', $time).'&SITE=1999888&RANG=32&AUTORISATION=XXXXXX&CODEREPONSE=00000&COMMENTAIRE=%s&REFABONNE=&PORTEUR='),
        ),
        'NUMTRANS=0005329117&NUMAPPEL=0010244812&NUMQUESTION='.sprintf('%010d', $time).'&SITE=1999888&RANG=32&AUTORISATION=XXXXXX&CODEREPONSE=00000&COMMENTAIRE=Demande trait�e avec succ�s&REFABONNE=&PORTEUR=',
        true);

        $parameters = array(
            'VERSION'     => '00104',
            'TYPE'        => '00001',
            'NUMQUESTION' => $time,
            'MONTANT'     => '1000',
            'DEVISE'      => '978',
            'REFERENCE'   => 'TestPaybox',
            'PORTEUR'     => '1111222233334444',
            'DATEVAL'     => '0520',
            'CVV'         => '222',
            'ACTIVITE'    => '024',
            'DATEQ'       => $time,
        );

        $this->request->setParameters($parameters);
        $result = $this->request->send();

        $this->assertEquals('00000', $result['CODEREPONSE']);
        $this->assertEquals('XXXXXX', $result['AUTORISATION']);

        /**
         * @wtf : I know...
         */
        $GLOBALS['NUMTRANS'] = $result['NUMTRANS'];
        $GLOBALS['NUMAPPEL'] = $result['NUMAPPEL'];
    }

    /**
     * @depends testSendSimpleAutorization
     */
    public function testSendCapture()
    {
        $time = time();

        $this->initMock(array(
            array('info', 'New API call.'),
            array('info', 'Url : https://preprod-ppps.paybox.com/PPPS.php'),
            array('info', 'Data :'),
            array('info', ' > VERSION = 00104'),
            array('info', ' > SITE = 1999888'),
            array('info', ' > RANG = 032'),
            array('info', ' > CLE = 1999888I'),
            array('info', ' > TYPE = 00002'),
            array('info', ' > NUMQUESTION = ' . sprintf('%010d', $time)),
            array('info', ' > MONTANT = 0000001000'),
            array('info', ' > DEVISE = 978'),
            array('info', ' > REFERENCE = TestPaybox'),
            array('info', ' > NUMTRANS = ' . $GLOBALS['NUMTRANS']),
            array('info', ' > NUMAPPEL = ' . $GLOBALS['NUMAPPEL']),
            array('info', ' > DATEQ = ' . sprintf('%014s', $time)),
            array('info', 'Result : NUMTRANS='.$GLOBALS['NUMTRANS'].'&NUMAPPEL='.$GLOBALS['NUMAPPEL'].'&NUMQUESTION='.sprintf('%010d', $time).'&SITE=1999888&RANG=32&AUTORISATION=XXXXXX&CODEREPONSE=00000&COMMENTAIRE=%s&REFABONNE=&PORTEUR='),
        ),
        'NUMTRANS=0005329117&NUMAPPEL=0010244812&NUMQUESTION='.sprintf('%010d', $time).'&SITE=1999888&RANG=32&AUTORISATION=XXXXXX&CODEREPONSE=00000&COMMENTAIRE=Demande trait�e avec succ�s&REFABONNE=&PORTEUR=',
        true);

        $parameters = array(
            'VERSION'     => '00104',
            'TYPE'        => '00002',
            'NUMQUESTION' => $time,
            'MONTANT'     => '1000',
            'DEVISE'      => '978',
            'REFERENCE'   => 'TestPaybox',
            'NUMTRANS'    => $GLOBALS['NUMTRANS'],
            'NUMAPPEL'    => $GLOBALS['NUMAPPEL'],
            'DATEQ'       => $time,
        );

        $this->request->setParameters($parameters);
        $result = $this->request->send();

        $this->assertEquals('00000', $result['CODEREPONSE']);
        $this->assertEquals('XXXXXX', $result['AUTORISATION']);
    }

    /**
     * @depends testSendCapture
     */
    public function testSendRefund()
    {
        $time = time();

        $this->initMock(array(
            array('info', 'New API call.'),
            array('info', 'Url : https://preprod-ppps.paybox.com/PPPS.php'),
            array('info', 'Data :'),
            array('info', ' > VERSION = 00104'),
            array('info', ' > SITE = 1999888'),
            array('info', ' > RANG = 032'),
            array('info', ' > CLE = 1999888I'),
            array('info', ' > TYPE = 00014'),
            array('info', ' > NUMQUESTION = ' . sprintf('%010d', $time)),
            array('info', ' > MONTANT = 0000001000'),
            array('info', ' > DEVISE = 978'),
            array('info', ' > REFERENCE = TestPaybox'),
            array('info', ' > NUMTRANS = ' . $GLOBALS['NUMTRANS']),
            array('info', ' > NUMAPPEL = ' . $GLOBALS['NUMAPPEL']),
            array('info', ' > ACTIVITE = 024'),
            array('info', ' > DATEQ = '.sprintf('%014s', $time)),
            array('info', 'Result : NUMTRANS=%s&NUMAPPEL='.$GLOBALS['NUMAPPEL'].'&NUMQUESTION='.sprintf('%010d', $time).'&SITE=1999888&RANG=32&AUTORISATION=XXXXXX&CODEREPONSE=00000&COMMENTAIRE=%s&REFABONNE=&PORTEUR='),
        ),
        'NUMTRANS=0005329136&NUMAPPEL=0010244812&NUMQUESTION='.sprintf('%010d', $time).'&SITE=1999888&RANG=32&AUTORISATION=XXXXXX&CODEREPONSE=00000&COMMENTAIRE=Demande trait�e avec succ�s&REFABONNE=&PORTEUR=',
        true);

        $parameters = array(
            'VERSION'     => '00104',
            'TYPE'        => '00014',
            'NUMQUESTION' => $time,
            'MONTANT'     => '1000',
            'DEVISE'      => '978',
            'REFERENCE'   => 'TestPaybox',
            'NUMTRANS'    => $GLOBALS['NUMTRANS'],
            'NUMAPPEL'    => $GLOBALS['NUMAPPEL'],
            'ACTIVITE'    => '024',
            'DATEQ'       => $time,
        );

        $this->request->setParameters($parameters);
        $result = $this->request->send();

        $this->assertEquals('00000', $result['CODEREPONSE']);
        $this->assertEquals('XXXXXX', $result['AUTORISATION']);
    }

    public function testSendSubscriberCreationButUserAlreadyExists()
    {
        $time = time();

        $this->initMock(array(
            array('info', 'New API call.'),
            array('info', 'Url : https://preprod-ppps.paybox.com/PPPS.php'),
            array('info', 'Data :'),
            array('info', ' > VERSION = 00104'),
            array('info', ' > SITE = 1999888'),
            array('info', ' > RANG = 032'),
            array('info', ' > CLE = 1999888I'),
            array('info', ' > TYPE = 00056'),
            array('info', ' > NUMQUESTION = ' . sprintf('%010d', $time)),
            array('info', ' > MONTANT = 0000001000'),
            array('info', ' > DEVISE = 978'),
            array('info', ' > REFERENCE = TestPaybox'),
            array('info', ' > REFABONNE = ABODOCUMENTATION001'),
            array('info', ' > ACTIVITE = 027'),
            array('info', ' > DATEQ = ' . sprintf('%014s', $time)),
            array('info', 'Result : NUMTRANS=0000000000&NUMAPPEL=0000000000&NUMQUESTION='.sprintf('%010d', $time).'&SITE=1999888&RANG=32&AUTORISATION=&CODEREPONSE=00016&COMMENTAIRE=%s&REFABONNE=ABODOCUMENTATION001&PORTEUR=1111222233334444'),
        ),
        'NUMTRANS=0000000000&NUMAPPEL=0000000000&NUMQUESTION='.sprintf('%010d', $time).'&SITE=1999888&RANG=32&AUTORISATION=&CODEREPONSE=00016&COMMENTAIRE=PAYBOX : Abonn� d�j� existant&REFABONNE=ABODOCUMENTATION001&PORTEUR=1111222233334444',
        true);

        $parameters = array(
            'VERSION'     => '00104',
            'TYPE'        => '00056',
            'NUMQUESTION' => $time,
            'MONTANT'     => '1000',
            'DEVISE'      => '978',
            'REFERENCE'   => 'TestPaybox',
            'PORTEUR'     => '1111222233334444',
            'DATEVAL'     => '0520',
            'CVV'         => '222',
            'REFABONNE'   => 'ABODOCUMENTATION001',
            'ACTIVITE'    => '027',
            'DATEQ'       => $time,
        );

        $this->request->setParameters($parameters);
        $result = $this->request->send();

        $this->assertEquals('00016', $result['CODEREPONSE']);
        $this->assertEquals('', $result['AUTORISATION']);
    }

    /**
     * @depends testSendSubscriberCreationButUserAlreadyExists
     */
    public function testSendSubscriberDebit()
    {
        $time = time();

        $this->initMock(array(
            array('info', 'New API call.'),
            array('info', 'Url : https://preprod-ppps.paybox.com/PPPS.php'),
            array('info', 'Data :'),
            array('info', ' > VERSION = 00104'),
            array('info', ' > SITE = 1999888'),
            array('info', ' > RANG = 032'),
            array('info', ' > CLE = 1999888I'),
            array('info', ' > TYPE = 00053'),
            array('info', ' > NUMQUESTION = ' . sprintf('%010d', $time)),
            array('info', ' > MONTANT = 0000001000'),
            array('info', ' > DEVISE = 978'),
            array('info', ' > REFERENCE = TestPaybox'),
            array('info', ' > REFABONNE = ABODOCUMENTATION001'),
            array('info', ' > ACTIVITE = 027'),
            array('info', ' > DATEQ = ' . sprintf('%014s', $time)),
            array('info', 'Result : NUMTRANS=%s&NUMAPPEL=%s&NUMQUESTION='.sprintf('%010d', $time).'&SITE=1999888&RANG=32&AUTORISATION=XXXXXX&CODEREPONSE=00000&COMMENTAIRE=%s&REFABONNE=ABODOCUMENTATION001&PORTEUR=1111222233334444'),
        ),
        'NUMTRANS=0005329164&NUMAPPEL=0010244857&NUMQUESTION='.sprintf('%010d', $time).'&SITE=1999888&RANG=32&AUTORISATION=XXXXXX&CODEREPONSE=00000&COMMENTAIRE=Demande trait�e avec succ�s&REFABONNE=ABODOCUMENTATION001&PORTEUR=1111222233334444',
        true);

        $parameters = array(
            'VERSION'     => '00104',
            'TYPE'        => '00053',
            'NUMQUESTION' => $time,
            'MONTANT'     => '1000',
            'DEVISE'      => '978',
            'REFERENCE'   => 'TestPaybox',
            'PORTEUR'     => '1111222233334444',
            'DATEVAL'     => '0520',
            'REFABONNE'   => 'ABODOCUMENTATION001',
            'ACTIVITE'    => '027',
            'DATEQ'       => $time,
        );

        $this->request->setParameters($parameters);
        $result = $this->request->send();

        $this->assertEquals('00000', $result['CODEREPONSE']);
        $this->assertEquals('XXXXXX', $result['AUTORISATION']);
    }
}
