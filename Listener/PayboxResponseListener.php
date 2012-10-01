<?php

namespace  Lexik\Bundle\PayboxBundle\Listener;

use Symfony\Component\Filesystem\Filesystem;

use Lexik\Bundle\PayboxBundle\Event\PayboxResponseEvent;

/**
 * A dummy sample listener that create a file for each ipn call.
 */
class PayboxResponseListener
{
    private $rootDir;

    private $filsystem;

    /**
     * Constructor.
     *
     * @param string     $rootDir
     * @param Filesystem $filesystem
     */
    public function __construct($rootDir, Filesystem $filesystem)
    {
        $this->rootDir    = $rootDir;
        $this->filesystem = $filesystem;
    }

    /**
     * Dummy sample.
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
