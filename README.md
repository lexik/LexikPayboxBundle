LexikPayboxBundle
=================

[![Build Status](https://secure.travis-ci.org/lexik/LexikPayboxBundle.png)](http://travis-ci.org/lexik/LexikPayboxBundle)
[![Project Status](https://stillmaintained.com/lexik/LexikPayboxBundle.png)](https://stillmaintained.com/lexik/LexikPayboxBundle)
[![Latest Stable Version](https://poser.pugx.org/lexik/paybox-bundle/v/stable.svg)](https://packagist.org/packages/lexik/paybox-bundle)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/378718a0-ea77-4592-89eb-9bf47214efc9/mini.png)](https://insight.sensiolabs.com/projects/378718a0-ea77-4592-89eb-9bf47214efc9)

LexikPayboxBundle makes the use of [Paybox](http://www.paybox.com) payment system easier by doing all the boring things for you.

LexikPayboxBundle silently does :
 * hmac hash calculation of parameters during request.
 * server testing before request to be sure it is up.
 * signature verification with openssl on ipn response.
 * triggers an event on response.

You only need to provide parameters of your transaction, customize the response page
and wait for the event triggered on ipn response.

Requirements
------------

 * PECL hash >= 1.1
 * openssl enabled

Installation
------------

Installation with composer :

```json
    ...
    "require": {
        ...
        "lexik/paybox-bundle": "dev-master",
        ...
    },
    ...
```

Add this bundle to your app/AppKernel.php :

``` php
public function registerBundles()
{
    return array(
        // ...
        new Lexik\Bundle\PayboxBundle\LexikPayboxBundle(),
        // ...
    );
}
```

Configuration
-------------

Your personnal account informations must be set in your config.yml

```yml
# Lexik Paybox Bundle
lexik_paybox:
    parameters:
        production: false        # Switches between Paybox test and production servers (preprod-tpe <> tpe)
        site:        '9999999'   # Site number provided by the bank
        rank:        '99'        # Rank number provided by the bank
        login:       '999999999' # Customer's login provided by Paybox
        idmerchant:  '999999999' # Id number provided by the bank.
        return_path: 'your.website.com/index' # Client 3dSecure redirection (RemoteMpi)
        redirect_path: 'your.website.com/listener' # Server to Server 3dSecure redirection (RemoteMpi)
        deferred:    '005'       # days number of payment deferred
        hmac:
            key: '01234...BCDEF' # Key used to compute the hmac hash, provided by Paybox
```

Additional configuration:

```yml
lexik_paybox:
    parameters:
        currencies:  # Optionnal parameters, this is the default value
            - '036'  # AUD
            - '124'  # CAD
            - '756'  # CHF
            - '826'  # GBP
            - '840'  # USD
            - '978'  # EUR
        hmac:
            algorithm:      sha512 # signature algorithm
            signature_name: Sign   # customize the signature parameter name
```

The routing collection must be set in your routing.yml

```yml
# Lexik Paybox Bundle
lexik_paybox:
    resource: '@LexikPayboxBundle/Resources/config/routing.yml'
```

```yml
# Lexik Paybox Bundle DirectPlus
lexik_paybox:
    resource: '@LexikPayboxBundle/Resources/config/routing_direct_plus.yml'
```

```yml
# Lexik Paybox Bundle 3D Secure RemoteMpi
lexik_paybox:
    resource: '@LexikPayboxBundle/Resources/config/routing_remote_mpi.yml'
```

Usage of Paybox System
----------------------

The bundle includes a sample controller `SampleController.php` with two actions.

```php
...
/**
 * Sample action to call a payment.
 * It create the form to submit with all parameters.
 */
public function callAction()
{
    $paybox = $this->get('lexik_paybox.request_handler');
    $paybox->setParameters(array(
        'PBX_CMD'          => 'CMD'.time(),
        'PBX_DEVISE'       => '978',
        'PBX_PORTEUR'      => 'test@paybox.com',
        'PBX_RETOUR'       => 'Mt:M;Ref:R;Auto:A;Erreur:E',
        'PBX_TOTAL'        => '1000',
        'PBX_TYPEPAIEMENT' => 'CARTE',
        'PBX_TYPECARTE'    => 'CB',
        'PBX_EFFECTUE'     => $this->generateUrl('lexik_paybox_sample_return', array('status' => 'success'), true),
        'PBX_REFUSE'       => $this->generateUrl('lexik_paybox_sample_return', array('status' => 'denied'), true),
        'PBX_ANNULE'       => $this->generateUrl('lexik_paybox_sample_return', array('status' => 'canceled'), true),
        'PBX_RUF1'         => 'POST',
        'PBX_REPONDRE_A'   => $this->generateUrl('lexik_paybox_ipn', array('time' => time()), true),
    ));

    return $this->render(
        'LexikPayboxBundle:Sample:index.html.twig',
        array(
            'url'  => $paybox->getUrl(),
            'form' => $paybox->getForm()->createView(),
        )
    );
}
...
/**
 * Sample action of a confirmation payment page on witch the user is sent
 * after he seizes his payment informations on the Paybox's platform.
 * This action must only containts presentation logic.
 */
public function responseAction($status)
{
    return $this->render(
        'LexikPayboxBundle:Sample:return.html.twig',
        array(
            'status'     => $status,
            'parameters' => $this->getRequest()->query,
        )
    );
}
...
```

The getUrl() method silently does a server check and throws an exception if the destination server does not respond.

The payment confirmation in your business logic must be done when the instant payment notification (IPN) occurs.
The plugin contains a controller with an action that manages this IPN and triggers an event.
The event contains all data transmetted during the request and a boolean that tells if signature verification was successful.

The bundle contains a listener example that simply create a file on each ipn call.

```php
namespace Lexik\Bundle\PayboxBundle\Listener;

use Symfony\Component\Filesystem\Filesystem;

use Lexik\Bundle\PayboxBundle\Event\PayboxResponseEvent;

/**
 * Simple listener that create a file for each ipn call.
 */
class PayboxResponseListener
{
    private $rootDir;

    private $filesystem;

    /**
     * Constructor.
     *
     * @param string     $rootDir
     * @param Filesystem $filesystem
     */
    public function __construct($rootDir, Filesystem $filesystem)
    {
        $this->rootDir = $rootDir;
        $this->filesystem = $filesystem;
    }

    /**
     * Creates a txt file containing all parameters for each IPN.
     *
     * @param  PayboxResponseEvent $event
     */
    public function onPayboxIpnResponse(PayboxResponseEvent $event)
    {
        $path = $this->rootDir . '/../data/' . date('Y\/m\/d\/');
        $this->filesystem->mkdir($path);

        $content = sprintf("Signature verification : %s\n", $event->isVerified() ? 'OK' : 'KO');
        foreach ($event->getData() as $key => $value) {
            $content .= sprintf("%s:%s\n", $key, $value);
        }

        file_put_contents($path . time() . '.txt', $content);
    }
}
```

To create your own listener, you just have to make it wait for the "paybox.ipn_response" event.
For example the listener of the bundle:

```yml
parameters:
    lexik_paybox.sample_response_listener.class: 'Lexik\Bundle\PayboxBundle\Listener\SampleIpnListener'

services:
    ...
    lexik_paybox.sample_response_listener:
        class: %lexik_paybox.sample_response_listener.class%
        arguments: [ %kernel.root_dir%, @filesystem ]
        tags:
            - { name: kernel.event_listener, event: paybox.ipn_response, method: onPayboxIpnResponse }
```


```php
namespace Lexik\Bundle\PayboxBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DirectPlusController
 *
 * @package Lexik\Bundle\PayboxBundle\Controller
 *
 * @author Romain Marecat <romain.marecat@gmail.com>
 */
class DirectPlusController extends Controller
{
    /**
     * Euro
     */
    const PBX_DEVISE_EURO = '978';

    /**
     * Different version of Paybox direct
     */
    const VERSION_DIRECT            = '00103';
    const VERSION_DIRECT_PLUS       = '00104';
    /**
     * ECL(Electronic Commerce Indicator).
     * Type of ordering items. Need for some banks.
     * 024 - request by internet
     */
    const PBX_ACTIVITE_VALUE = '024';

    /**
     * All payment direct action listed
     */
    const PBX_PAYMENT_ACTION_AUTHORIZE = '00001';
    const PBX_PAYMENT_ACTION_DEBIT = '00002';
    const PBX_PAYMENT_ACTION_AUTHORIZE_CAPTURE = '00003';
    const PBX_PAYMENT_ACTION_CREDIT = '00004';
    const PBX_PAYMENT_ACTION_CANCELLATION = '00005';
    const PBX_PAYMENT_ACTION_CHECK_EXIST_TRANSACTION = '00011';
    const PBX_PAYMENT_ACTION_TRANSACTION_WHITOUT_AUTHORIZE = '00012';
    const PBX_PAYMENT_ACTION_UPDATE_AMOUNT_TRANSACTION = '00013';
    const PBX_PAYMENT_ACTION_REFUND = '00014';
    const PBX_PAYMENT_ACTION_READ = '00017';

    /**
     * All payment direct plus action listed
     */
    const PBX_PAYMENT_ACTION_AUTHORIZE_ON_SUBSCRIBER = '00051';
    const PBX_PAYMENT_ACTION_DEBIT_ON_SUBSCRIBER = '00052';
    const PBX_PAYMENT_ACTION_AUTHORIZE_CAPTURE_ON_SUBSCRIBER = '00053';
    const PBX_PAYMENT_ACTION_CREDIT_ON_SUBSCRIBER = '00054';
    const PBX_PAYMENT_ACTION_CANCEL_ON_SUBSCRIBER = '00055';
    const PBX_PAYMENT_ACTION_REGISTER_SUBSCRIBER = '00056';
    const PBX_PAYMENT_ACTION_UPDATE_EXIST_SUBSRIBER = '00057';
    const PBX_PAYMENT_ACTION_DELETE_SUBSCRIBER = '00058';
    const PBX_PAYMENT_ACTION_TRANSACTION_WHITOUT_AUTHORIZE_FORCE = '00061';

    /**
     * $transactionNumber NUMTRANS returned by Authorize
     * @var string
     */
    protected $transactionNumber;

    /**
     * $callNumber NUMAPPEL returned by Authorize
     * @var string
     */
    protected $callNumber;

    /**
     * [generateRequestNumber description]
     * @return [type] [description]
     */
    protected function generateRequestNumber()
    {
        $secs = (date('G') * 3600) + (date('i') * 60) + date('s');
        return (string) $secs . rand(10, 99);
    }

    /**
     * authorizeAction
     * @return array response      Paybox Authorization client
     */
    public function authorizeAction()
    {
        $paybox = $this->get('lexik_paybox.direc_plus.request_handler');
        $parameters = [
            'VERSION'               => self::VERSION_DIRECT_PLUS,
            'TYPE'                  => self::PBX_PAYMENT_ACTION_AUTHORIZE,
            'DATEQ'                 => sprintf('%014d', time()),
            'NUMQUESTION'           => $this->generateRequestNumber(),
            'MONTANT'               => sprintf('%010d', str_pad(100 * 100, 10, '0', STR_PAD_LEFT)),
            'DEVISE'                => self::PBX_DEVISE_EURO,
            'REFERENCE'             => 'ORDER' . rand(1000, 9999),
            'PORTEUR'               => sprintf('%016d', '1111222233334444'),
            'DATEVAL'               => sprintf('%04d', '0117'),
            'CVV'                   => sprintf('%03d', '123'),
            'ACTIVITE'              => self::PBX_ACTIVITE_VALUE,
            'DIFFERE'               => sprintf('%03d', '001'),
        ];

        if ($this->id3d) {
            $parameters['ID3D'] = $this->id3d;
        }

        $paybox->setParameters($parameters);

        $response = array_map("utf8_encode", $paybox->send());

        if (isset($response['NUMTRANS']) && isset($response['NUMAPPEL'])) {
            $this->transactionNumber = $response['NUMTRANS'];
            $this->callNumber = $response['NUMTRANS'];
        }

        return $this->render(
            'LexikPayboxBundle:DirectPlus:index.html.twig',
            array(
                'response' => $response,
            )
        );
    }

    /**
     * captureAction
     * @return array response    Paybox debit client
     */
    public function captureAction()
    {
        $paybox = $this->get('lexik_paybox.direc_plus.request_handler');
        $parameters = [
            'VERSION'               => self::VERSION_DIRECT_PLUS,
            'TYPE'                  => self::PBX_PAYMENT_ACTION_DEBIT,
            'NUMQUESTION'           => $this->generateRequestNumber(),
            'MONTANT'               => sprintf('%010d', str_pad(100 * 100, 10, '0', STR_PAD_LEFT)),
            'DEVISE'                => self::PBX_DEVISE_EURO,
            'REFERENCE'             => 'ORDER' . rand(1000, 9999),
            'DATEQ'                 => sprintf('%014d', time()),
            'NUMTRANS'              => $this->transactionNumber,
            'NUMAPPEL'              => $this->callNumber,
            'ACTIVITE'              => self::PBX_ACTIVITE_VALUE,
            'DIFFERE'               => sprintf('%03d', '001'),
        ];

        if ($this->id3d) {
            $parameters['ID3D'] = $this->id3d;
        }

        $paybox->setParameters($parameters);
        $response = array_map("utf8_encode", $paybox->send());

        return $this->render(
            'LexikPayboxBundle:DirectPlus:index.html.twig',
            array(
                'response' => $response,
            )
        );
    }
```

Resources
---------

All transactions parameters are available in the [official documentation](http://www1.paybox.com/telechargement_focus.aspx?cat=3).
