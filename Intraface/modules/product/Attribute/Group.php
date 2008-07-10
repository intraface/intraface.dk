<?php
/**
 * Handles attribute groups to products
 *
 * @package  Intraface
 * @author   Sune Jensen <sj@sunet.dk>
 * @since    
 * @version  @package-version@
 */
class Intraface_modules_product_Attribute_Group extends Doctrine_Record
{
    
    public function setTableDefinition()
    {
        $this->setTableName('product_attribute_group');
        $this->hasColumn('name', 'string', 255, array('notblank' => true, 'nohtml' => ''));
    }
    
    public function setUp()
    {
        $this->loadTemplate('Intraface_Doctrine_Template_Intranet');
        $this->actAs('SoftDelete');
        
        $this->hasMany('Intraface_modules_product_Attribute as attribute', array('local' => 'id',
                                            'foreign' => 'attribute_group_id'));
        
    }

    public function getId()
    {
        return $this->id;
    }
    
    public function getName() 
    {
        return $this->name;
    }
    
    public function undelete()
    {
        $this->deleted = false;
        $this->save();
    }
    
    /**
     * Returns collection of attributes
     */
    public function getAttributes()
    {
        /**
         * @todo: Make sure not used before saved.
         */
        
        return Doctrine_Query::create()
            ->select('name, id')
            ->from('Intraface_modules_product_Attribute')
            ->where('attribute_group_id = '.$this->getId())
            ->orderBy('position')
            ->execute();
    }
    
    /**
     * Returns attribute record from id
     * 
     * @param integer $id id
     */
    public function getAttribute($id)
    {
        $attribute = Doctrine::getTable('Intraface_modules_product_Attribute')->find(intval($id));
        if(!$attribute) {
            throw new Intraface_Gateway_Exception('Invalid attribute id '.intval($id));
        }
        return $attribute;
        
    }
}
