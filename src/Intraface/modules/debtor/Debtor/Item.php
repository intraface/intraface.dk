<?php

/**
 * Intraface_modules_debtor_Debtor_Item
 *
 *
 * @property integer $id
 * @property integer $intranet_id
 * @property integer $debtor_id
 * @property integer $product_id
 * @property integer $product_detail_id
 * @property integer $product_variation_id
 * @property integer $product_variation_detail_id
 * @property string $description
 * @property float $quantity
 * @property integer $position
 * @property integer $active
 *
 * @package    Intraface
 * @subpackage Intraface_modules_debtor
 * @author     Sune Jensen sune@intraface.dk
 */
class Intraface_modules_debtor_Debtor_Item extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('debtor_item');
        $this->hasColumn('id', 'integer', 4, array('type' => 'integer', 'length' => 4, 'primary' => true, 'autoincrement' => true));
        $this->hasColumn('intranet_id', 'integer', 4, array('type' => 'integer', 'length' => 4, 'default' => '0', 'notnull' => true));
        $this->hasColumn('debtor_id', 'integer', 4, array('type' => 'integer', 'length' => 4, 'default' => '0', 'notnull' => true));
        $this->hasColumn('product_id', 'integer', 4, array('type' => 'integer', 'length' => 4, 'default' => '0', 'notnull' => true));
        $this->hasColumn('product_detail_id', 'integer', 4, array('type' => 'integer', 'length' => 4, 'default' => '0', 'notnull' => true));
        $this->hasColumn('product_variation_id', 'integer', 4, array('type' => 'integer', 'length' => 4, 'default' => '0', 'notnull' => true));
        $this->hasColumn('product_variation_detail_id', 'integer', 4, array('type' => 'integer', 'length' => 4, 'default' => '0', 'notnull' => true));
        $this->hasColumn('description', 'string', null, array('type' => 'string', 'notnull' => true));
        $this->hasColumn('quantity', 'float', 11, array('type' => 'float', 'length' => 11, 'default' => '0.00', 'notnull' => true));
        $this->hasColumn('position', 'integer', 4, array('type' => 'integer', 'length' => 4, 'default' => '0', 'notnull' => true));
        $this->hasColumn('active', 'integer', 4, array('type' => 'integer', 'length' => 4, 'default' => '1', 'notnull' => true));
    }
    
    public function setUp()
    {
        $this->actAs('Intraface_Doctrine_Template_Intranet');
        
        $this->hasOne(
            'Intraface_modules_product_ProductDoctrine as product',
            array('local' => 'product_id', 'foreign' => 'id')
        );
            
        /* $this->hasMany('Intraface_modules_product_Variation as variation',
            array('local' => 'id', 'foreign' => 'product_id'));  */
    }
    
    /**
     * Returns product object
     * @return object Intraface_modules_product_ProductDoctrine
     */
    public function getProduct()
    {
        return $this->product;
    }
    
    /**
     * Returns product detail object
     * @return object Intraface_modules_product_Product_Details
     */
    public function getProductDetail()
    {
        return $this->getProduct()->getDetails($this->product_detail_id);
    }
    
    /**
     * Returns object, where it is possible to get the product price.
     * @return object
     */
    private function getProductPriceObject()
    {
        if ($this->getProduct()->hasVariation()) {
            # Make sure we we have loaded the correct details
            $this->getProduct()->getDetails($this->product_detail_id);
            
            return $this->getProduct()->getVariation($this->product_variation_id)->getDetail($this->product_variation_detail_id);
        } else {
            return $this->getProduct()->getDetails($this->product_detail_id);
        }
    }
    
    /**
     * Returns the amount of the item on the debtor
     * @return object Ilib
     */
    public function getAmount()
    {
        return new Ilib_Variable_Float($this->getQuantity()->getAsIso(2) * $this->getProductPriceObject()->getPriceIncludingVat($this->getProduct())->getAsIso(2), 'iso');
    }
    
    public function getQuantity()
    {
        return new Ilib_Variable_Float($this->quantity, 'iso');
    }
}
