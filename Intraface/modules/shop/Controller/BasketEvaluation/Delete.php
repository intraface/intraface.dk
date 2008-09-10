<?php
class Intraface_modules_shop_Controller_BasketEvaluation_Delete extends k_Controller
{
    function getShop()
    {
        return $this->context->getShop();
    }
    
    function GET()
    {
        $basketevaluation = new Intraface_modules_shop_BasketEvaluation($this->registry->get('db'), $this->registry->get('intranet'), $this->getShop(), (int)$this->context->name);
        if($basketevaluation->getId() == 0) {
            throw new exception('Invalid basket evaluation '.$this->context->name);
        }
        $basketevaluation->delete();
        
        throw new k_http_Redirect($this->url('../../'));
        
    }
}