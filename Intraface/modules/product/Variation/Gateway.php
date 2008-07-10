<?php
/**
 * Gateway to get Variation
 */

/**
 * Gateway to get variation
 */
class Intraface_modules_product_Variation_Gateway
{
    /**
     * @var object variation;
     */
    private $variation;
    
    /**
     * @var array groups attached to product;
     */
    private $groups;
    
    
    /**
     * @var object Product
     */
    private $product;
    
    /**
     * Constructor
     * 
     * @param object Intraface_modules_product_Product
     * @return void
     */
    public function __construct($product)
    {
        if(!$product->get('has_variation')) {
            throw new Exception('You can not get variation for a product without variations!');
        }
        
        $this->product = $product;
        $this->groups = $product->getAttributeGroups();
        
        if(count($this->groups) == 0) {
            throw new Exception('No groups is added to the product');
        } elseif(count($this->groups) == 1) {
            $this->variation = new Intraface_modules_product_Variation_OneAttributeGroup;
            
        } elseif(count($this->groups) == 2) {
            $this->variation =  new Intraface_modules_product_Variation_TwoAttributeGroups;
        } else {
            throw new Exception('At the moment the system only supports up to two attribute groups!');
        }
    }
    
    /**
     * Returns empty variation object
     * 
     * @return object Intraface_modules_product_Variation_OneAttributeGroup or Intraface_modules_product_Variation_TwoAttributeGroups
     *  
     */
    public function getObject()
    {
        return $this->variation;
    }
    
    /**
     * Find a variation from id
     * 
     * @return object Intraface_modules_product_Variation_OneAttributeGroup or Intraface_modules_product_Variation_TwoAttributeGroups
     */
    public function findById($id)
    {
        $query = $this->variation->getTable()->createQuery();
        
        $select = get_class($this->variation).'.*, detail.*, a1.*, a1_attribute.*, a1_attribute_group.*';
        if(count($this->groups) == 2) $select .= ', a2.*, a2_attribute.*, a2_attribute_group.*';
        $query = $query->select($select)
            ->leftJoin(get_class($this->variation).'.detail detail')
            ->innerJoin(get_class($this->variation).'.attribute1 a1 WITH a1.attribute_number = 1')
            ->innerJoin('a1.attribute a1_attribute')
            ->innerJoin('a1_attribute.group a1_attribute_group');
        
        if(count($this->groups) == 2) {
            $query->innerJoin(get_class($this->variation).'.attribute2 a2 WITH a2.attribute_number = 2')
                ->innerJoin('a2.attribute a2_attribute')
                ->innerJoin('a2_attribute.group a2_attribute_group');
        }
        
        $collection = $query->where(get_class($this->variation).'.product_id = ? AND id = ?', array($this->product->getId(), $id))->execute();
        
        if(!$collection || $collection->count() == 0) {
            throw new Intraface_Gateway_Exception('Unable to find variation');
        }
        if($collection->count() > 1) {
            throw new Exception('More than one entry found!');
        }
        return $collection->getFirst();    
    }
    
    /**
     * Find a variation from attributes
     * 
     * @return object Intraface_modules_product_Variation_OneAttributeGroup or Intraface_modules_product_Variation_TwoAttributeGroups
     */
    public function findByAttributes($value)
    {
        $query = $this->variation->getTable()->createQuery();
        $query = $query->select(get_class($this->variation).'.*, detail.*')
            ->leftJoin(get_class($this->variation).'.detail detail')
            ->innerJoin(get_class($this->variation).'.attribute1 a1 WITH a1.attribute_number = 1 AND a1.product_attribute_id = ?', array($value['attribute1']));
        
        if(count($this->groups) == 2) {
            $query->innerJoin(get_class($this->variation).'.attribute2 a2 WITH a2.attribute_number = 2 AND a2.product_attribute_id = ?', array($value['attribute2']));
        }
        
        $collection = $query->where(get_class($this->variation).'.product_id = ?', $this->product->getId())->execute();
        
        if(!$collection || $collection->count() == 0) {
            throw new Intraface_Gateway_Exception('Unable to find variation');
        }
        if($collection->count() > 1) {
            throw new Exception('More than one entry found!');
        }
        return $collection->getFirst();
    } 
    
    
    /**
     * Finds all variations for product
     * 
     * @return object Doctrine_Collection
     */
    public function findAll() {
        
        $query = $this->variation->getTable()->createQuery();
        
        $select = get_class($this->variation).'.*, detail.*, a1.*, a1_attribute.*, a1_attribute_group.*';
        if(count($this->groups) == 2) $select .= ', a2.*, a2_attribute.*, a2_attribute_group.*';
        
        $query = $query->select($select)
            ->leftJoin(get_class($this->variation).'.detail detail')
            ->innerJoin(get_class($this->variation).'.attribute1 a1 WITH a1.attribute_number = 1')
            ->innerJoin('a1.attribute a1_attribute')
            ->innerJoin('a1_attribute.group a1_attribute_group')
            ->orderBy('a1_attribute.position');
        
        if(count($this->groups) == 2) {
            $query->innerJoin(get_class($this->variation).'.attribute2 a2 WITH a2.attribute_number = 2')
                ->innerJoin('a2.attribute a2_attribute')
                ->innerJoin('a2_attribute.group a2_attribute_group')
                ->orderBy('a1_attribute.position, a2_attribute.position');
            
        }
        $query = $query->where(get_class($this->variation).'.product_id = ?', $this->product->getId());
        // echo $query->getSqlQuery();
        $collection = $query->execute();
        
        if(!$collection || $collection->count() == 0) {
            throw new Intraface_Gateway_Exception('Unable to find variation');
        }
        
        return $collection;
    } 
}

?>