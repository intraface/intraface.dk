<?php

class Intraface_modules_currency_Currency extends Doctrine_Record
{
    
    private $type;
    
    public function setTableDefinition()
    {
        $this->setTableName('currency');
        $this->hasColumn('type_key', 'integer', 11, array('greaterthan' => 0));
    }
    
    public function setUp()
    {
        $this->actAs('Intraface_Doctrine_Template_Intranet');
        $this->actAs('SoftDelete');
        $this->hasMany('Intraface_modules_currency_Currency_ExchangeRate_ProductPrice as product_price_exchange_rate', 
            array('local' => 'id', 'foreign' => 'currency_id'));
        
        $this->hasMany('Intraface_modules_currency_Currency_ExchangeRate_Payment as payment_exchange_rate', 
            array('local' => 'id', 'foreign' => 'currency_id'));
    
    }
    
    public function setType($type) 
    {
        $this->type_key = $type->getKey();
    }
    
    public function getType()
    {
        if (!$this->type_key) {
            throw new Exception('You need to load a currency to get the type');
        }
        
        if (!$this->type) {
            $type_gateway = new Intraface_modules_currency_Currency_Type;
            $this->type = $type_gateway->getByKey($this->type_key);
        }
        
        return $this->type;
    }
    
    
    
    public function getProductPriceExchangeRate($id = 0)
    {
        if ($id == 0) {
            return $this->product_price_exchange_rate->getLast();
        } 
        else {
            if (false === ($key = array_search($id, $this->product_price_exchange_rate->getPrimaryKeys()))) {
                throw new Intraface_Gateway_Exception('Unable to find exchange rate with id '.$id);
            }
            
            return $this->product_price_exchange_rate[$key];
        }
    }
    
    public function getPaymentExchangeRate($id = 0)
    {
        if ($id == 0) {
            return $this->payment_exchange_rate->getLast();
        } 
        else {
            if (false === ($key = array_search($id, $this->payment_exchange_rate->getPrimaryKeys()))) {
                throw new Intraface_Gateway_Exception('Unable to find exchange rate with id '.$id);
            }
            
            return $this->payment_exchange_rate[$key];
        }
    }
    
    public function getId()
    {
        return $this->id;
    }
    
    protected function validate() 
    {
        if ($this->getTable()->findOneByTypeKey($this->type_key)) {
            $this->getErrorStack()->add('type', 'it is already added');
        }            
    }  
}