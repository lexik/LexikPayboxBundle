<?php

namespace Lexik\Bundle\PayboxBundle\Listener;

use Lexik\Bundle\PayboxBundle\Event\PayboxResponseEvent;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Sample listener that create a file for each ipn call.
 *
 * @author Olivier Maisonneuve <o.maisonneuve@lexik.fr>
 */
class SampleIpnListener
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
        $path = sprintf('%s/../data/%s', $this->rootDir, date('Y\/m\/d\/'));
        $this->filesystem->mkdir($path);

        $content = sprintf('Signature verification : %s%s', $event->isVerified() ? 'OK' : 'KO', PHP_EOL);
        foreach ($event->getData() as $key => $value) {
            $content .= sprintf("%s:%s%s", $key, $value, PHP_EOL);
        }

        file_put_contents(
            sprintf('%s%s.txt', $path, time()),
            $content
        );
    }
}
