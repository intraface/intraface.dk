<?php

class Intraface_modules_product_Attribute_Group_Gateway
{
    
    public function __construct() 
    {
        
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
     * Returns Record
     */
    public function findDeletedById($id)
    {
        
        $groups = Doctrine_Query::create()
            ->select('id, name, intranet_id')
            ->from('Intraface_modules_product_Attribute_Group')
            ->addWhere('Intraface_modules_product_Attribute_Group.deleted = 1')
            ->addWhere('id = '.intval($id))
            ->execute();
        
        if($groups->count() == 0) {
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
        if($groups->count() == 0) {
            throw new Intraface_Gateway_Exception('Invalid group id "'.$id.'" in findById');
        }
        return $groups->getFirst();
    }
    
}

?>
