<?php

namespace Lexik\Bundle\PayboxBundle\Listener;

use Symfony\Component\Filesystem\Filesystem;

use Lexik\Bundle\PayboxBundle\Event\PayboxResponseEvent;

/**
 * Sample listener that create a file for each ipn call.
 *
 * @author Lexik <dev@lexik.fr>
 */
class PayboxResponseListener
{
    /**
     * @var string
     */
    private $rootDir;

    /**
     * @var Filesystem
     */
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
     * @param PayboxResponseEvent $event
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
