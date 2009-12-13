<?php
class Intraface_modules_shop_Shop_Gateway
{
    public function __construct()
    {

    }

    /**
     * Find all shops
     *
     * @return object Doctrine_Collection
     */
    public function findAll()
    {
        return Doctrine_Query::create()
            ->select('*')
            ->from('Intraface_modules_shop_Shop')
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
        return Doctrine::getTable('Intraface_modules_shop_Shop')
            ->find(intval($id));
    }
}
