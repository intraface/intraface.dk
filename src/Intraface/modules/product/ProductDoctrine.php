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
    public $active_details;

    /**
     * Doctrine table definitin
     *
     * @return void
     */
    public function setTableDefinition()
    {
        $this->setTableName('product');
        $this->hasColumn('do_show', 'integer', 1, array('type' => 'integer', 'length' => 1, 'default' => '1', 'notnull' => true));
        $this->hasColumn('has_variation', 'integer', 1, array('type' => 'integer', 'length' => 1, 'default' => '0', 'notnull' => true));
        $this->hasColumn('active', 'integer', 1, array('type' => 'integer', 'length' => 1, 'default' => '1', 'notnull' => true));
        $this->hasColumn('changed_date', 'timestamp', null, array('type' => 'timestamp', 'default' => new Doctrine_Expression('NOW()'), 'notnull' => true));
        // $this->hasColumn('quantity', 'integer', 4, array('type' => 'integer', 'length' => 4, 'default' => '0', 'notnull' => true));
        $this->hasColumn('stock', 'integer', 1, array('type' => 'integer', 'length' => 1, 'default' => '0', 'notnull' => true));
        // $this->hasColumn('locked', 'integer', 1, array('type' => 'integer', 'length' => 1, 'default' => '0', 'notnull' => true));
    }

    public function preInsert($event)
    {
        $this->changed_date = new Doctrine_Expression('NOW()');
        # If details are not valid, we do not want to save the product.
        if (is_object($this->active_details)) {
            /*
            # We make sure translation is added before insert as name is required
            if ($this->active_details->Translation->count() == 0) {
                throw new Exception('Details Translations needs to be set. Use getDetails()->Translation');
            }

            # We make sure translations is valid
            foreach ($this->active_details->Translation AS $translation) {
                if (!$translation->isValid()) {
                    throw new Doctrine_Validator_Exception(array());
                }
            }*/

            # We validate the details for errors before we insert
            if (!$this->active_details->isValid(true)) {
                # It must contain errors in more than product_id for us to respond.
                if ($this->active_details->getErrorStack()->count() > 1 && $this->active_details->getErrorStack()->contains('product_id')) {
                    throw new Doctrine_Validator_Exception(array());
                }
            }
        } else {
            throw new Exception('Product details needs to be filled! use getDetails()');
            /*
            # We make sure, that the details is loaded, as they are required to insert product.
            $this->getErrorStack()->add('name', 'must be filled in');
            throw new Doctrine_Validator_Exception(array());
            */
        }
    }

    public function getCollectedErrorStack()
    {
        $stack =& $this->getErrorStack();
        if (is_object($this->active_details)) {
            foreach ($this->active_details->getErrorStack() as $field => $errors) {
                foreach ($errors as $error) {
                    $stack->add($field, $error);
                }
            }
            foreach ($this->active_details->Translation as $language => $translation) {
                foreach ($translation->getErrorStack() as $field => $errors) {
                    foreach ($errors as $error) {
                        $stack->add($field.'_'.$language, $error);
                    }
                }
            }
        }

        if ($stack->count() > 1 && $stack->contains('product_id')) {
            $stack->remove('product_id');
        }

        return $stack;
    }

    public function setUp()
    {
        $this->actAs('Intraface_Doctrine_Template_Intranet');

        $this->hasMany(
            'Intraface_modules_product_Product_Details as details',
            array('local' => 'id', 'foreign' => 'product_id')
        );

        $this->hasMany(
            'Intraface_modules_product_Variation_UniversalAttributeGroups as variation',
            array('local' => 'id', 'foreign' => 'product_id')
        );
            
        $this->hasMany(
            'Intraface_modules_product_Product_X_Attribute_Group as x_attribute_group',
            array('local' => 'id', 'foreign' => 'product_id')
        );
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
        return $this->has_variation;
    }

    /**
     * returns variation
     *
     * @todo The variations should be loaded in the ProductGateway instead of here.
     */
    public function getVariation($id = 0)
    {
        $gateway = new Intraface_modules_product_Variation_Gateway($this);
        if (intval($id) > 0) {
            return $gateway->findById($id);
        }
        $object = $gateway->getObject();
        unset($gateway);
        $object->product_id = $this->getId();
        return $object;
        
        /*if ($id != 0) {
            foreach ($this->variation AS $variation) {
                if ($variation->getId() == $id) {
                    return $variation;
                }
            }
            throw new Exception('Unable to find variation with id '.$id);
        }

        $variation = $this->variation->get(NULL); // returns empty variation;
        $variation->product_id = $this->getId();
        return $variation;
        */
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
    function getDetails($id = null)
    {
        if ($id == null) {
            if (empty($this->active_details)) {
                if ($this->details->count() == 0) {
                    // Add an empty details entry.
                    $this->active_details = $this->details->get(null);
                } else {
                    // Find the active details
                    foreach ($this->details as $details) {
                        if ($details->active == 1) {
                            $this->active_details = $details;
                        }
                    }
                    if (empty($this->active_details)) {
                        throw new Exception('Unable to find active details');
                    }
                }
            }

            return $this->active_details;
        } else {
            foreach ($this->details as $detail) {
                if ($detail->getId() == $id) {
                    return $this->active_details = $detail;
                }
            }
            throw new Exception('Unable to find detail with id '.$id);
        }
    }

    /**
     * Returns whether product is shown
     * @return integer 1 or 0
     */
    public function showInShop()
    {
        return $this->do_show;
    }

    /**
     * Make sures active details is also refreshed.
     *
     * @param $depth boolean true or false
     * @return void
     */
    public function refresh($depth = false)
    {
        $this->active_details = null;
        parent::refresh($depth);
    }
    
    /**
     * Set attribute for product
     *
     * @param integer $id     Attribute id to relate to this product
     *
     * @return boolean
     */
    public function setAttributeGroup(Intraface_modules_product_Attribute_Group $attribute_group)
    {
        if (!$this->hasVariation()) {
            throw new Exception('You can not set attribute group for a product without variations!');
        }
        
        $crosses = $this->x_attribute_group->getTable()
            ->createQuery()
            ->select('id')
            ->where('product_id = ? AND product_attribute_group_id = ?', array($this->getId(), $attribute_group->getId()))
            ->execute();
        
        if ($crosses->count() == 1) {
            return true;
        }
        
        $cross = $this->x_attribute_group->get(null); // returns empty object
        $cross->product_attribute_group_id = $attribute_group->getId();
        $cross->save();
        
        return true;
    }
    
    /**
     * Get all attributes related to the product
     *
     * @todo Rewrite product_x_attribute_group to Doctrine.
     * @todo Add a field named attribute_number to product_x_attribute_group, to be sure
     *       that a attribute always relates to the correct attribute number on the variation.
     *
     * @return array
     */
    public function getAttributeGroups()
    {
        if (!$this->hasVariation()) {
            throw new Exception('You can not get attribute groups for a product without variations!');
        }
        
        $cross = $this->x_attribute_group->get(null);
        $attribute_groups = $cross->attribute_group->getTable()
            ->createQuery()
            ->select('Intraface_modules_product_Attribute_Group.*')
            ->innerJoin('Intraface_modules_product_Attribute_Group.x_product attribute_group_x_product')
            ->where('attribute_group_x_product.product_id = ?', $this->getId())
            ->orderBy('Intraface_modules_product_Attribute_Group.id')
            ->execute();
   
        return $attribute_groups;
    }
}
