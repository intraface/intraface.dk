<?php
class Intraface_modules_shop_PaymentMethods extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('shop_paymentmethods');
        $this->hasColumn('paymentmethod_key', 'integer',  11);
        $this->hasColumn('text',  'string',  65555);
        $this->hasColumn('shop_id',   'integer',  11);
    }
    
    public function setUp()
    {
        $this->actAs('Intraface_Doctrine_Template_Intranet');
        $this->loadTemplate('Intraface_Doctrine_Template_Intranet');
        $this->hasOne('Intraface_modules_shop_Shop', array('local' => 'shop_id',
                                    'foreign' => 'id'));
    }

    function getId()
    {
        return $this->id;
    }
    
    public function getPaymentMethodKey() 
    {
        return $this->paymentmethod_key;
    }
    
    function getText()
    {
        return $this->text;
    }
}