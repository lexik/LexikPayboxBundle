LexikPayboxBundle
=================

[![Build Status](https://secure.travis-ci.org/lexik/LexikPayboxBundle.png)](http://travis-ci.org/lexik/LexikPayboxBundle)
![Project Status](http://stillmaintained.com/lexik/LexikPayboxBundle.png)

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/378718a0-ea77-4592-89eb-9bf47214efc9/big.png)](https://insight.sensiolabs.com/projects/378718a0-ea77-4592-89eb-9bf47214efc9)

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
        site:  '9999999'   # Site number provided by the bank
        rank:  '99'        # Rank number provided by the bank
        login: '999999999' # Customer's login provided by Paybox
        hmac:
            key: '01234...BCDEF' # Key used to compute the hmac hash, provided by Paybox

        # Optionnal parameters, this is the default value
        currencies:
            - '036' # AUD
            - '124' # CAD
            - '756' # CHF
            - '826' # GBP
            - '840' # USD
            - '978' # EUR
        validation_by: url_ipn
```

The routing collection must be set in your routing.yml

```yml
# Lexik Paybox Bundle
lexik_paybox:
    resource: '@LexikPayboxBundle/Resources/config/routing.yml'
```

Usage
-----

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
services:
    ...
    lexik_paybox.sample_response_listener:
        class: %lexik_paybox.sample_response_listener.class%
        arguments: [ %kernel.root_dir%, @filesystem ]
        tags:
            - { name: kernel.event_listener, event: paybox.ipn_response, method: onPayboxIpnResponse }
```

Production
----------

By default, getUrl() returns the preproduction url.
To toggle in production, you just need to specify 'prod' in parameter of the getUrl('prod') method.

Validation IPN response
-----------------------

For security, the status returned by PBX_EFFECTUE, PBX_REFUSE, PBX_ANNULE and PBX_ATTENTE, should
not be trusted as it can by altered by malicous user. You must instead use IPN notification.
IPN notification is send directly from Paybox server to the URL you specified either in PBX_REPONDRE_A
option or in Paybox interface.

If you use PBX_REPONDRE_A option you must specify in your `config.yml` file the following parameters :

```yml
lexik_paybox:
    parameters:
        validation_by: pbx_retour
        pbx_retour: Mt:M;Ref:R;Auto:A;Erreur:E  # report the PBX_RETOUR option you defined in your code
```

If you use the Paybox interface, you can let the default parameters :

```yml
lexik_paybox:
    parameters:
        validation_by: url_ipn
```

Resources
---------

All transactions parameters are available in the [official documentation](http://www1.paybox.com/telechargement_focus.aspx?cat=3).
