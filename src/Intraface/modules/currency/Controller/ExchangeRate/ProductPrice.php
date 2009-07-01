<?php
class Intraface_modules_currency_Controller_ExchangeRate_ProductPrice extends k_Controller
{
    
    /**
     * Returns translations object
     * 
     * @return object Translation
     */
    public function getTranslation()
    {
        return $this->context->getTranslation();
    }
    
    public function getCurrency()
    {
        return $this->context->getCurrency();
    }
    
    function GET()
    {
        return 'Intentionally left blank';
    }

    function forward($name)
    {
        if ($name == 'update') {
            $next = new Intraface_modules_currency_Controller_ExchangeRate_Update($this, $name);
            return $next->handleRequest();
        }
        
        throw new Exception('No valid forwards was found!');

    }
}