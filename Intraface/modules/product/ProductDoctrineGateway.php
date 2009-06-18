<?php
/**
 * Doctrine Gateway to ProductDoctrine
 *
 * Bruges til at holde styr på varerne.
 *
 * @package Intraface_Product
 * @author Sune Jensen
 * @see ProductDoctrine
 */

class Intraface_modules_product_ProductDoctrineGateway
{
    
    /**
     * @var object
     */
    private $user;

    /**
     * 
     * @var object doctrine record table
     */
    private $table;
    
    /**
     * Constructor
     *
     * @param object  $user                Userobject
     *
     * @return void
     */
    function __construct($doctrine, $user)
    {
        
        $this->user = $user;
        $this->table = $doctrine->getTable('Intraface_modules_product_ProductDoctrine');
    }

    /**
     * Finds a product with an id
     *
     * @param integer $id product id
     * @return object
     */
    function findById($id)
    {
        
        $collection = $this->table
            ->createQuery()
            ->select('*, details.*')
            ->innerJoin('Intraface_modules_product_ProductDoctrine.details AS details')
            ->addWhere('active = 1')
            ->addWhere('id = ?', $id)
            ->addOrderBy('details.id')
            ->execute();
    
        if ($collection == NULL || $collection->count() != 1) {
            throw new Intraface_Gateway_Exception('Error finding product from id '.$id);
        } else {
            return $collection->getFirst();
        }
        
    }

    /**
     * Finds all products
     *
     * Hvis den er fra webshop bør den faktisk opsamle oplysninger om søgningen
     * så man kan se, hvad folk er interesseret i.
     * Søgemaskinen skal være tolerant for stavefejl
     *
     * @param object $search 
     *
     * @return object collection containing products
     */
    public function findBySearch($search)
    {
        
    }
    
    
    public function getMaxNumber()
    {
        $collection = $this->table
            ->createQuery()
            ->select('id, details.number')
            ->innerJoin('Intraface_modules_product_ProductDoctrine.details AS details')
            ->addWhere('Intraface_modules_product_ProductDoctrine.active = 0 OR Intraface_modules_product_ProductDoctrine.active = 1')
            ->orderBy('details.number')
            ->execute();
    
        if ($collection == NULL || $collection->count() == 0) {
            return 0;
        } else {
            return $collection->getLast()->getDetails()->getNumber();
        }
    }
    
    
}
