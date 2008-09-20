<?php

class Intraface_modules_currency_Currency_Gateway
{
    private $table;
    
    public function __construct($doctrine) 
    {
        $this->table = $doctrine->getTable('Intraface_modules_currency_Currency');
    }
    
    /**
     * Finds all currencies
     * 
     * @return object collection
     */
    public function findAll()
    {
        $query = $this->table->createQuery('currency');
        $collection = $query->select('currency.*, product_price_exchange_rate.*, payment_exchange_rate.*')
                ->leftJoin('currency.product_price_exchange_rate AS product_price_exchange_rate INDEX BY product_price_exchange_rate.id') 
                ->leftJoin('currency.payment_exchange_rate AS payment_exchange_rate INDEX by payment_exchange_rate.id')
                ->orderBy('type_key')
                ->execute();
        
        
        if (!$collection || $collection->count() == 0) {
            throw new Intraface_Gateway_Exception('Unable to find currency');
        }
        return $collection;
    }
    
    public function findById($id) 
    {
        $query = $this->table->createQuery('currency');
        $query = $query->select('currency.*, product_price_exchange_rate.*, payment_exchange_rate.*')
                ->leftJoin('currency.product_price_exchange_rate AS product_price_exchange_rate INDEX BY product_price_exchange_rate.id')
                ->leftJoin('currency.payment_exchange_rate AS payment_exchange_rate INDEX by payment_exchange_rate.id')
                ->addWhere('currency.id = ?', $id);
        
        // echo $query->getSqlQuery();
        $collection = $query->execute();
        
        if (!$collection || $collection->count() != 1) {
            throw new Intraface_Gateway_Exception('Unable to find currency '.$id);
        }
        return $collection->getFirst();
    }
}
?>
