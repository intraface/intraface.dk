<?php
class Intraface_modules_shop_Controller_Categories_Show extends k_Controller
{
    
    function forward($name) 
    {
        if ($name == 'edit') {
            $next = new Intraface_modules_shop_Controller_Categories_Edit($this, $name);
            return $next->handleRequest();
        }
    }
}