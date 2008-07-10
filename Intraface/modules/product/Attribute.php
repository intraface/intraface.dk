<?php
/**
 * Handles attributes to products
 *
 * @package  Intraface
 * @author   
 * @since    
 * @version  @package-version@
 */
class Intraface_modules_product_Attribute extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('product_attribute');
        $this->hasColumn('attribute_group_id', 'integer', 11);
        $this->hasColumn('name', 'string', 255, array('notblank' => true, 'nohtml' => ''));
        $this->hasColumn('position', 'integer', 11);
    }
    
    public function setUp()
    {
        
        $this->loadTemplate('Intraface_Doctrine_Template_Intranet');
        $this->actAs('SoftDelete');
        $this->actAs('Positionable');
        $this->hasOne('Intraface_modules_product_Attribute_Group as group', array('local' => 'attribute_group_id',
                                            'foreign' => 'id'));
    }

    function getId()
    {
        return $this->id;
    }
    
    function getName() 
    {
        return $this->name;
    }

}