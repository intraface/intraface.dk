<?php
/**
 * Class that crosses attribute group with product
 * 
 * @todo Should be renamed Intraface_modules_product_Product_X_AttributeGroup
 * 
 * @author Sune Jensen <sune@intraface.dk>
 *
 */
class Intraface_modules_product_Product_X_Attribute_Group extends Doctrine_Record
{
    
    
    public function setTableDefinition()
    {
        $this->setTableName('product_x_attribute_group');
        $this->hasColumn('product_id', 'integer', 11, array('greaterthan' => 0));
        $this->hasColumn('product_attribute_group_id', 'integer', 11, array('greaterthan' => 0));
    }
    
    public function setUp()
    {
        $this->actAs('Intraface_Doctrine_Template_Intranet');
        
        $this->hasOne('Intraface_modules_product_Attribute_Group as attribute_group', 
            array('local' => 'product_attribute_group_id','foreign' => 'id'));
    }
    
}