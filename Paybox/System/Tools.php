<?php

namespace Lexik\Bundle\PayboxBundle\Paybox\System;

/**
 * Class Tools
 *
 * @package Lexik\Bundle\PayboxBundle\Paybox\System
 *
 * @author Lexik <dev@lexik.fr>
 * @author Olivier Maisonneuve <o.maisonneuve@lexik.fr>
 */
class Tools
{
    /**
     * Makes an array of parameters become a querystring like string.
     *
     * @param  array $array
     *
     * @return string
     */
    static public function stringify(array $array)
    {
        $result = array();

        foreach ($array as $key => $value) {
            $result[] = sprintf('%s=%s', $key, $value);
        }

        return implode('&', $result);
    }
}
