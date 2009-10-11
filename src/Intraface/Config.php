<?php
class Intraface_Config
{
    function __get($var)
    {
        return $this->{$var};
    }

    function __set($var, $value)
    {
        $this->{$var} = $value;
    }
}