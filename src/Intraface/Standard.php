<?php
/**
 * Standard
 *
 * En standardklasse med fælles funktioner. Alle klasser kan nedarve fra denne
 * klasse.
 *
 * @package Intraface
 * @author  Lars Olesen <lars@legestue.net>
 * @since   0.1.0
 * @version @package-version@
 *
 */
class Intraface_Standard
{
    public $value;

    function __construct()
    {
        // init
    }

    function get($key = '')
    {
        if (!empty($key)) {
            if (isset($this->value[$key])) {
                return($this->value[$key]);
            } else {
                return '';
            }
        }
        return $this->value;
    }
}
