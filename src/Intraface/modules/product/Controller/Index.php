<?php
class Intraface_modules_product_Controller_Index extends k_Component
{
    /*
    function GET()
    {
    	return 'intentionally left blank';
    }

    protected function forward($name)
    {
        $next = new Intraface_modules_product_Controller_Show($this, $name);
        return $next->handleRequest();
    }
    */

    function map($name)
    {
        return 'Intraface_modules_product_Controller_Selectproduct';
    }
}