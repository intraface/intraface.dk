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
        //$this->loadTemplate('Intraface_Doctrine_Template_Intranet');
        $this->actAs('Intraface_Doctrine_Template_Intranet');
        $this->actAs('SoftDelete');
        
        $this->hasMany('Intraface_modules_product_Attribute as attribute', 
            array('local' => 'id', 'foreign' => 'attribute_group_id')
        );
        
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
        // $this->deleted_at = new Doctrine_Null;
        // $this->save();
        
        $result = $this->getTable()
            ->createQuery()
            ->update()
            ->set('deleted_at', 'NULL')
            ->addWhere('id = ?', $this->getId())
            ->addWhere('intranet_id = ?', $this->intranet_id)
            ->execute();
        
        if(!$result) {
            throw new Exception('Error deleting group '.$this->getId());
        }
        
    }
    
    /**
     * Returns collection of attributes
     */
    public function getAttributes()
    {
        if (!$this->id) {
            throw new Exception('Can first be used when group is saved');
        }
        
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
        if (!$attribute) {
            throw new Intraface_Gateway_Exception('Invalid attribute id '.intval($id));
        }
        return $attribute;
        
    }
    
    /**
     * Returns collection of attributes used on a given product
     * 
     * @param object $product Intraface_modules_product_Product
     */
    public function getAttributesUsedByProduct($product)
    {
        if (!$this->id) {
            throw new Exception('Can first be used when group is saved');
        }
        
        return Doctrine_Query::create()
            ->select('attribute.name, attribute.id')
            ->from('Intraface_modules_product_Attribute attribute')
            ->innerJoin('attribute.variation_x_attribute variation_x_attribute')
            ->innerJoin('variation_x_attribute.variation variation')
            ->addWhere('attribute.attribute_group_id = ?', $this->getId())
            ->addWhere('variation.product_id = ?', $product->getId())
            ->groupBy('attribute.id')
            ->orderBy('position')
            ->execute();
    }
}
