<?php
class Intraface_modules_shop_Controller_BasketEvaluation_Show extends k_Controller
{
    
    function getShop()
    {
        return $this->context->getShop();
    }
    
    function GET()
    {
        
        $basketevaluation = new Intraface_modules_shop_BasketEvaluation($this->registry->get('db'), $this->registry->get('intranet'), $this->getShop(), $this->name);
        if ($basketevaluation->getId() == 0) {
            throw new Exception('Invalid basket evaluation '.$this->name);
        }    
        
        
        throw new Exception('No content on this page!');
    }

    function forward($name)
    {
        if ($name == 'edit') {
            $next = new Intraface_modules_shop_Controller_BasketEvaluation_Edit($this, $name);
            return $next->handleRequest();
        } elseif ($name == 'delete') {
            $next = new Intraface_modules_shop_Controller_BasketEvaluation_Delete($this, $name);
            return $next->handleRequest();
        }

        throw new Exception('Unknown forward');
    }
}