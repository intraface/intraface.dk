<?php

class Intraface_modules_debtor_PaymentMethod
{
    /**
     * @var array The possible payment types
     * 
     * Do not change the key for the payment methods
     */
    private $types = array(
            1 => 'BankTransfer',
            2 => 'GiroPayment01',
            3 => 'GiroPayment71',
            4 => 'CashOnDelivery',
            5 => 'OnlinePayment'
    );
    
    
    /**
     * Returns specific payment method
     */
    public function getByName($method, $id = 0)
    {
        if(!ereg("^[a-zA-Z0-9]+$")) {
            throw new Exception('Invalid method name "'.$method.'"');
        }
        
        $name = 'Intraface_modules_debtor_PaymentMethod_'.$method;
        
        return new $name;
        
    }
    
    /**
     * Returns a payment method by id
     */
    public function getById($key, $id = 0)
    {
        if(!isset($this->types[$key])) {
            throw new Exception('Invalid payment method id');
        }
        
        return $this->getByName($this->types[$key]);
    }
    
    /**
     * Returns possible payment methods
     */
    public function getAll()
    {
        $types = array();
        foreach($this->types AS $key => $type) {
            $types[$key] = $this->getByName($type);
        }
        return $types;
    }
    
    
    
}



?>
