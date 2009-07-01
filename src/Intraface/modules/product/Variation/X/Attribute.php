<?php

class Intraface_modules_product_Variation_X_Attribute extends Doctrine_Record
{
    
    
    public function setTableDefinition()
    {
        $this->setTableName('product_variation_x_attribute');
        $this->hasColumn('product_variation_id', 'integer', 11, array('greaterthan' => 0));
        $this->hasColumn('product_attribute_id', 'integer', 11, array('greaterthan' => 0));
        $this->hasColumn('attribute_number', 'integer', 11, array('greaterthan' => 0));
        
    
    }
    
    public function setUp()
    {
        //$this->loadTemplate('Intraface_Doctrine_Template_Intranet');
        $this->actAs('Intraface_Doctrine_Template_Intranet');
        
        $this->hasOne('Intraface_modules_product_Attribute as attribute', array('local' => 'product_attribute_id','foreign' => 'id'));
        // Notice that Variation/OneAttributeGroup and TwoAttributeGroup extends Variation, which means we do not get
        // all functionality in the relation specified below here. 
        $this->hasOne('Intraface_modules_product_Variation as variation', array('local' => 'product_variation_id','foreign' => 'id'));
        
    }
    
}


?>