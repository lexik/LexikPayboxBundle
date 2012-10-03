LexikPayboxBundle
=================

LexikPayboxBundle eases the implementation of Paybox payment system ([http://www.paybox.com])
and does all the boring thing for you.

LexikPayboxBundle silently does :
 * hmac hash calculation of parameters during request.
 * server testing before request to be sure it is up.
 * signature verification with openssl on ipn response.
 * trigger an event on response.

You only need to provide parameters of your transaction, customize the response page
and wait for the event triggered on ipn response.

Requirements
------------

 * PECL hash >= 1.1
 * openssl enabled

Installation
------------

Installation with composer.

        "require": {
            ...
            "lexik/paybox-bundle": "dev-develop",
            ...
        },

Configuration
-------------

Your personnal account informations must be set in your config.yml

    # Lexik Paybox Bundle
    lexik_paybox:
        parameters:
            site:  '9999999'   # Site number provided by the bank
            rank:  '99'        # Rank number provided by the bank
            login: '999999999' # Customer's login provided by Paybox
            hmac:
                key: '01234...BCDEF' # Key used to compute the hmac hash, provided by Paybox

Usage
-----

The bundle includes a sample controller with two actions.

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
            'PBX_TYPECARTE'    => 'CB,VISA,EUROCARD_MASTERCARD',
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

The payment confirmation is done by an instant payment notification (IPN).
The plugin contains a controller with an action that manages this IPN and trigger an event.
The event contains all data transmeted during the request and a boolean that tells if signature verification was successful.

The plugin contains a listener exemple that simply create a file on each ipn call.

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

To create your own listener, you just have to make it wait for the "paybox.ipn_response" event.
For exemple the exemple listener of the bundle :

    services:
        ...
        lexik_paybox.sample_response_listener:
            class: %lexik_paybox.sample_response_listener.class%
            arguments: [ %kernel.root_dir%, @filesystem ]
            tags:
                - { name: kernel.event_listener, event: paybox.ipn_response, method: onPayboxIpnResponse }

Resources
---------

All transactions parameters are available in the official documentation.

    [http://www1.paybox.com/telechargement_focus.aspx?cat=3]
