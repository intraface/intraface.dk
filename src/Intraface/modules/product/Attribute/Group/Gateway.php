<?php
/**
 * Gateway to find Attribute groups.
 * 
 * @todo Should be renamed Intraface_modules_product_AttributeGroupGateway
 * 
 * @author sune
 *
 */
class Intraface_modules_product_Attribute_Group_Gateway
{
    /**
     * Doctrine_Table object
     * @var object
     */
    private $table;
    
    /**
     * Constructor
     * 
     * @todo $doctrine should not be optional
     * 
     * @param object $doctrine Doctrine_Connection 
     * @return void
     */
    public function __construct($doctrine = NULL) 
    {
        /**
         * @todo remove id and make $doctrine required 
         */
        if($doctrine != NULL) {
            $this->table = $doctrine->getTable('Intraface_modules_product_Attribute_Group');
        }
    }
    
    /**
     * returns collection
     */
    public function findAll()
    {
        // NOTE: Very important that it is ordered by id so the groups
        // does always get attached to the correct attribute number on the variation.
        return Doctrine_Query::create()
            ->select('id, name')
            ->from('Intraface_modules_product_Attribute_Group')
            ->orderBy('id')->execute();
    }
    
    /**
     * Returns attribute group from attribute id
     * 
     * @param integer $id attribute id
     * @return object Doctrine_Record
     */
    public function findByAttributeId($id)
    {
        
        $groups = $this->table->createQuery()
            ->select('id, name')
            ->innerJoin('Intraface_modules_product_Attribute_Group.attribute attribute')
            ->where('attribute.id = ?', (int)$id)
            ->orderBy('id')
            ->execute();
        
        if ($groups->count() == 0) {
            throw new Intraface_Gateway_Exception('Unable to find group from attribute id "'.$id.'"');
        }
        return $groups->getFirst();
            
    }
    
    /**
     * Returns Record
     */
    public function findDeletedById($id)
    {
        
        $groups = Doctrine_Query::create()
            ->select('id, name, intranet_id')
            ->from('Intraface_modules_product_Attribute_Group')
            ->addWhere('Intraface_modules_product_Attribute_Group.deleted_at IS NOT NULL')
            ->addWhere('id = '.intval($id))
            ->execute();
        
        if ($groups->count() == 0) {
            throw new Intraface_Gateway_Exception('Invalid group id "'.$id.'" to undelete');
        }
        return $groups->getFirst();
    }
    
    /**
     * Returns record
     */
    public function findById($id)
    {
        $groups = Doctrine::getTable('Intraface_modules_product_Attribute_Group')->findById(intval($id));
        if ($groups->count() == 0) {
            throw new Intraface_Gateway_Exception('Invalid group id "'.$id.'" in findById');
        }
        return $groups->getFirst();
    }
    
    
    
}
