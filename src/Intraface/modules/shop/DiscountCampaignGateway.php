<?php
/**
 * Not finished. Can be removed if costumers no longer interested 6/3 2010 /Sune
 * @author sune
 *
 */
class Intraface_modules_shop_DiscountCampaignGateway
{
    
    /**
     * Constructor
     *
     * @param object $doctrine Doctrine
     * @param object $user Userobject
     *
     * @return void
     */
    function __construct($doctrine, $user)
    {
        $this->user = $user;
        $this->table = $doctrine->getTable('Intraface_modules_shop_DiscountCampaign');
    }
    
    /**
     * Finds all campaigns
     * 
     * @return Doctrine_Collection
     */
    public function findAll()
    {
        $collection = $this->table
            ->createQuery()
            ->select('id, name')
            ->addOrderBy('name')
            ->execute();
        
        return $collection;
    }
    
    
    
    /**
     * Finds record from id
     * 
     * @return Intraface_modules_shop_DiscountCampaign
     */
    public function findById($id)
    {
        $collection = $this->table
            ->createQuery()
            ->select('id, name')
            ->addWhere('id = ?', $id)
            ->execute();
        
        if ($collection->count() == 0) {
            throw new Intraface_Gateway_Exception('Invalid campaign id "'.$id.'" in findById');
        }
        
        return $collection->getFirst();
    }
    
}
?>