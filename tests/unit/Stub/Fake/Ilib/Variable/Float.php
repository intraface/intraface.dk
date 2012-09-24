<?php
class Fake_Ilib_Variable_Float 
{
    protected $iso;
    protected $amount;
    
    function __construct($amount)
    {
        $this->amount = $amount;
    }
    
    function getAsIso() 
    {
        return $this->amount;
    }

    function getAsLocale()
    {
        return number_format((float)$this->amount, 2, ",", ".");
    }
}
