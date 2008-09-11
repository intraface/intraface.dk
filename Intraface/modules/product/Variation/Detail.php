<?php

class Intraface_modules_product_Variation_Detail extends Doctrine_Record
{
    
    
    public function setTableDefinition()
    {
        $this->setTableName('product_variation_detail');
        $this->hasColumn('date_created', 'datetime', array());
        $this->hasColumn('product_variation_id', 'integer', 11, array());
        $this->hasColumn('price_difference', 'integer', 11, array());
        $this->hasColumn('weight_difference', 'integer', 11, array());
        
    
    }
    
    public function setUp()
    {
        //$this->loadTemplate('Intraface_Doctrine_Template_Intranet');
        $this->actAs('Intraface_Doctrine_Template_Intranet');
        // This relation is skippend because of several variation classes
        // $this->hasOne('Intraface_modules_product_Variation as variation', array('local' => 'product_variation_id', 'foreign' => 'id'));
    }
    
    public function preInsert()
    {
        $this->date_created = date('Y-m-d H:i:s');

    }
    public function preUpdate($event)
    {
        $new = new Intraface_modules_product_Variation_Detail;
        $new->product_variation_id = $this->product_variation_id;
        $new->price_difference = $this->price_difference;
        $new->weight_difference = $this->weight_difference;
        $new->save();
        
        $event->skipOperation();
    }

    public function getId()
    {
        return $this->id;
    }
    
    public function getPriceDifference()
    {
        return $this->price_difference;
    }
    
    public function getWeightDifference()
    {
        return $this->weight_difference;
    }
}


?>