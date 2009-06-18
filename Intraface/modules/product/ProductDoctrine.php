<?php
/**
 * Product
 *
 * To manage products in intraface
 *
 * @package Intraface_modules_product_Product
 * @author Sune Jensen
 * @see ProductDetail
 * @see Stock
 */

/**
 * Product
 *
 * To manage products in intraface
 *
 * @package Intraface_modules_product_Product
 * @author Sune Jensen
 * @see ProductDetail
 * @see Stock
 */
class Intraface_modules_product_ProductDoctrine extends Doctrine_Record
{
    
    /**
     * 
     * @var object with active details
     */
    private $active_details;
    
    /**
     * Doctrine table definitin
     * 
     * @return void
     */
    public function setTableDefinition()
    {
        $this->setTableName('product');
        // $this->hasColumn('do_show', 'integer', 1, array('type' => 'integer', 'length' => 1, 'default' => '1', 'notnull' => true));
        $this->hasColumn('has_variation', 'integer', 1, array('type' => 'integer', 'length' => 1, 'default' => '0', 'notnull' => true));
        $this->hasColumn('active', 'integer', 1, array('type' => 'integer', 'length' => 1, 'default' => '1', 'notnull' => true));
        $this->hasColumn('changed_date', 'timestamp', null, array('type' => 'timestamp', 'default' => '0000-00-00 00:00:00', 'notnull' => true));
        // $this->hasColumn('quantity', 'integer', 4, array('type' => 'integer', 'length' => 4, 'default' => '0', 'notnull' => true));
        $this->hasColumn('stock', 'integer', 1, array('type' => 'integer', 'length' => 1, 'default' => '0', 'notnull' => true));
        // $this->hasColumn('locked', 'integer', 1, array('type' => 'integer', 'length' => 1, 'default' => '0', 'notnull' => true));
    }
    
    public function preInsert($event)
    {
        $this->changed_date = new Doctrine_Expression('NOW()');
        # If details are not valid, we do not want to save the product.
        
        
        if(is_object($this->active_details)) {
            if(!$this->active_details->isValid()) {
                // throw new Doctrine_Validator_Exception('Unable to validate details');
                $event->skipOperation();
            }
        }
        
    }
    
    public function setUp()
    {
        $this->actAs('Intraface_Doctrine_Template_Intranet');
        
        $this->hasMany('Intraface_modules_product_Product_Details as details',
            array('local' => 'id', 'foreign' => 'product_id'));
            
        $this->hasMany('Intraface_modules_product_Variation as variation', array('local' => 'id', 'foreign' => 'product_id'));    
    }

    /**
     * Returns id of product
     * 
     * @return integer id
     */
    public function getId()
    {
        return $this->id;
    }
    
    public function hasStock()
    {
        return $this->stock;
    }
    
    /**
     * Returns stock
     * 
     * @return object Intrace_modules_stock_Stock
     */
    public function getStock()
    {
        throw new exception('Stock to be implemented');
    }
    
    /**
     * Returns whether product has Variation
     * 
     * @return boolean 1 or 0
     */
    public function hasVariation()
    {
        return $this->_has_variation;
    }
    
    /**
     * Returns whether product is active
     * @return boolean true or false;
     */
    public function isActive()
    {
        if ($this->active == 0) {
            return false;
        }
        return true;
    }
    
    /**
     * Gets the details
     *
     * @return object
     */
    function getDetails($id = NULL)
    {
        if($id == NULL) {
            if(empty($this->active_details)) {
                if($this->details->count() == 0) {
                    $this->active_details = $this->details->get(NULL);
                } else {
                    $this->active_details = $this->details->getLast();
            
                }
            }
            
            return $this->active_details;
        }
        else {
            return $this->active_details = $this->details[$id];
        }
    }

}

?>