<?php
class Intraface_modules_shop_Controller_Categories_Show extends k_Controller
{
    function getModel($id = 0)
    {
        return $this->context->getModel($id);
    }
    
    function forward($name) 
    {
        if ($name == 'edit') {
            $next = new Intraface_modules_shop_Controller_Categories_Edit($this, $name);
            return $next->handleRequest();
        }
    }
}