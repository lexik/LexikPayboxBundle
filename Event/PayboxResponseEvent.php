<?php

namespace Lexik\Bundle\PayboxBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class PayboxResponseEvent.
 */
class PayboxResponseEvent extends Event
{
    /**
     * @var array
     */
    private $data;

    /**
     * @var boolean
     */
    private $verified;

    /**
     * Constructor.
     *
     * @param array   $data
     * @param boolean $verified
     */
    public function __construct(array $data, $verified = false)
    {
        $this->data     = $data;
        $this->verified = (bool) $verified;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return boolean
     */
    public function isVerified()
    {
        return $this->verified;
    }
}
