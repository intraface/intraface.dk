<?php
class Intraface_modules_currency_Controller_Show extends k_Controller
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
        $gateway = new Intraface_modules_currency_Currency_Gateway($this->registry->get('doctrine'));
        $currency = $gateway->findById($this->name);
        if ($currency === false) {
            throw new Exception('Invalid currency '.$this->name);
        }
        return $currency;
    }
    
    function GET()
    {
        return 'No content';
    }

    function forward($name)
    {
        if ($name == 'exchangerate') {
            $next = new Intraface_modules_currency_Controller_ExchangeRate_Index($this, $name);
            return $next->handleRequest();
        }
        
        throw new Exception('No valid forwards was found!');

    }
}