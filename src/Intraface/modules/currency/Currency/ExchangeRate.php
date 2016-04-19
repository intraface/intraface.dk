<?php
class Intraface_modules_currency_Currency_ExchangeRate extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $used_for_range = count($this->getUsedForTypes());
        $this->setTableName('currency_exchangerate');
        $this->hasColumn('currency_id', 'integer', 11, array('greaterthan' => 0));
        $this->hasColumn('used_for_key', 'integer', 1, array('range' => array(1, $used_for_range)));
        $this->hasColumn('rate', 'double', 11, array('greaterthan' => 0));
        $this->hasColumn('date_created', 'timestamp', array('notnull'));

        $this->hasOne(
            'Intraface_modules_currency_Currency as currency',
            array('local' => 'currency_id', 'foreign' => 'id')
        );

        $this->setSubclasses(array(
            'Intraface_modules_currency_Currency_ExchangeRate_ProductPrice'  => array('used_for_key' => 1),
            'Intraface_modules_currency_Currency_ExchangeRate_Payment' => array('used_for_key' => 2)
        ));

    }

    public function setUp()
    {
        $this->actAs('Intraface_Doctrine_Template_Intranet');
    }

    public function preUpdate()
    {
        throw new Exception('You cannot update an exchange rate. Please add a new.');
    }

    public function preSave()
    {
        if ($this->date_created == null) {
            $this->date_created = date('Y-m-d h:i:s');
        }
    }

    public function setCurrency($currency)
    {
        $this->currency_id = $currency->getId();
    }

    public function setRate($rate)
    {
        $this->rate = $rate->getAsIso();
    }

    public function getRate()
    {
        return new Ilib_Variable_Float($this->rate, 'iso');
    }

    public function getId()
    {
        return $this->id;
    }

    public function getDateUpdated()
    {
        return new Ilib_Variable_String_DateTime($this->date_created);
    }

    public function delete()
    {
        throw new Exception('You cannot delete exchange rates.');
    }

    /**
     * Converts amount from system default to currency
     *
     * @param object $amount Ilib_Variable_Float cotaining amount
     * @return object Ilib_Varibale_Float containing converted amount
     */
    public function convertAmountToCurrency($amount)
    {
        return new Ilib_Variable_Float($amount->getAsIso() / ($this->getRate()->getAsIso() / 100));
    }
    
    
    /**
     * Converts amount from currency to system default
     *
     * @param object $amount Ilib_Variable_Float cotaining amount
     * @return object Ilib_Varibale_Float containing converted amount
     */
    public function convertAmountFromCurrency($amount)
    {
        return new Ilib_Variable_Float($amount->getAsIso() * ($this->getRate()->getAsIso() / 100));
    }

    public function getUsedForTypes()
    {
        return array(
            1 => 'productprice',
            2 => 'payment'
        );
    }
}
