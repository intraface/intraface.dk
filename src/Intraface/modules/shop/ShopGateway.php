<?php
class Intraface_modules_shop_ShopGateway
{
    private $table;
    
    public function __construct(Doctrine_Connection $doctrine)
    {
        $this->table = $doctrine->getTable('Intraface_modules_shop_Shop');
    }

    /**
     * Find all shops
     *
     * @return object Doctrine_Collection
     */
    public function findAll()
    {
        return $this->table
            ->createQuery()
            ->select('*')
            ->orderBy('name')
            ->execute();
    }

    /**
     * Returns record from
     *
     * @param integer $id id of shop
     *
     * @return object Doctrine_Record
     */
    public function findById($id)
    {
        $collection = $this->table
            ->createQuery()
            ->select('*')
            ->addWhere('id = ?', $id)
            ->execute();
        
        if ($collection == null || $collection->count() != 1) {
            throw new Intraface_Gateway_Exception('Error finding shop from id '.$id);
        } else {
            return $collection->getLast();
        }
    }
}
