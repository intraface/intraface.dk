<?php
/**
 * Class to get details about products. Works as versionable
 * 
 * @author Sune Jensen
 *
 */

/**
 * Class to get details about products. Works as versionable
 * 
 * @author Sune Jensen
 */
class Intraface_modules_product_Product_Details extends Doctrine_Record
{
    
    /**
     * Doctrine table definition 
     * 
     * @return void
     */
    public function setTableDefinition()
    {
        $this->setTableName('product_detail');
        $this->hasColumn('product_id', 'integer', 4, array('type' => 'integer', 'length' => 4, 'default' => '0', 'notnull' => true));
        $this->hasColumn('number', 'integer', 4, array('type' => 'integer', 'length' => 4, 'default' => '0', 'notnull' => true));
        $this->hasColumn('name', 'string', 255, array('type' => 'string', 'length' => 255, 'default' => '', 'notnull' => true, 'notblank' => true));
        $this->hasColumn('description', 'string', null, array('type' => 'string', 'notnull' => true));
        $this->hasColumn('price', 'float', 11, array('type' => 'float', 'length' => 11, 'default' => '0.00', 'notnull' => true));
        $this->hasColumn('before_price', 'float', 11, array('type' => 'float', 'length' => 11, 'default' => '0.00', 'notnull' => true));
        $this->hasColumn('weight', 'integer', 4, array('type' => 'integer', 'length' => 4, 'default' => '0', 'notnull' => true));
        $this->hasColumn('unit', 'integer', 4, array('type' => 'integer', 'length' => 4, 'default' => '0', 'notnull' => true));
        $this->hasColumn('vat', 'integer', 1, array('type' => 'integer', 'length' => 1, 'default' => '1', 'notnull' => true));
        // $this->hasColumn('show_unit', 'enum', 3, array('type' => 'enum', 'length' => 3, 'values' => array(0 => 'Yes', 1 => 'No'), 'default' => 'No', 'notnull' => true));
        // $this->hasColumn('pic_id', 'integer', 4, array('type' => 'integer', 'length' => 4, 'default' => '0', 'notnull' => true));
        $this->hasColumn('changed_date', 'timestamp', null, array('type' => 'timestamp', 'default' => '0000-00-00 00:00:00', 'notnull' => true));
        $this->hasColumn('do_show', 'integer', 1, array('type' => 'integer', 'length' => 1, 'default' => '1', 'notnull' => true));
        $this->hasColumn('active', 'integer', 4, array('type' => 'integer', 'length' => 4, 'default' => '0', 'notnull' => true));
        $this->hasColumn('state_account_id', 'integer', 4, array('type' => 'integer', 'length' => 4, 'default' => '0', 'notnull' => true));
    }
    
    /**
     * Doctrine setup
     * 
     * @return void
     */
    public function setUp()
    {
        $this->actAs('Intraface_Doctrine_Template_Intranet');
        
        // $this->hasOne('Intraface_modules_product_ProductDoctrine as product', array('local' => 'product_id', 'foreign' => 'id'));
    }
    
    function validate()
    {
        // print($this->state().'d'..'ff');
        if(is_object($this->product_id)) {
            $product_id = 0;
        } else {
            $product_id = $this->product_id;
        }
        
        $collection = $this->getTable()
            ->createQuery()
            ->select('id')
            ->innerJoin('Intraface_modules_product_ProductDoctrine.details AS details')
            ->addWhere('active = 1')
            ->addWhere('details.active = 1')
            ->addWhere('details.number = ?', $this->number)
            ->addWhere('details.product_id <> '. $product_id)
            ->execute();
            
        if ($collection->count() > 0) {
            $this->getErrorStack()->add('number', 'it is already added');
        }
        
    }
    
    public function preSave($event)
    {
        if(is_object($this->price)) {
            $this->price = $this->price->getAsIso();
        }
        
        if(is_object($this->before_price)) {
            $this->before_price = $this->before_price->getAsIso();
        }
        
        if(is_object($this->weight)) {
            $this->weight = $this->weight->getAsIso();
        }
        
        if(is_null($this->description)) $this->description = '';
        
        if($this->state_account_id == '') $this->state_account_id = 0;
    }
    
    public function preInsert($event)
    {
        
        if(empty($this->number)) {
            /**
             * @TODO: We should have gateway as parameter instead
             */
            $gateway = new Intraface_modules_product_ProductDoctrineGateway($this->getTable()->getConnection(), NULL);
            $this->number = $gateway->getMaxNumber() + 1;
        }
        
        $this->active = 1;
        $this->changed_date = new Doctrine_Expression('NOW()');
        
    }
    
    public function preUpdate($event)
    {
        $values = $this->toArray();
        unset($values['id']);
        unset($values['changed_date']);
        $new = new Intraface_modules_product_Product_Details;
        $new->fromArray($values);
        $new->save();
        
        $this->refresh();
        $this->active = 0;
    }

    /**
     * Returns id
     * 
     * @return integer id
     */
    function getId()
    {
        return $this->id;
    }
    
    /**
     * Returns name of product
     * 
     * @return string name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns number of product
     * 
     * @return integer number
     */
    public function getNumber()
    {
        return $this->number;
    }
    
    public function getDescription()
    {
        return $this->description;
    }
    
    /**
     * Returns the price of the product
     *
     * @return object Ilib_Variable_Float with price
     */
    public function getPrice()
    {
        if(is_object($this->price)) {
            return $this->price;
        }
        return new Ilib_Variable_Float($this->price);
    }

    /**
     * Returns the price of the product in given currency
     *
     * @param object $currency Intraface_modules_currency_Currency
     * @param integer $exchange_rage_id
     *
     * @return obejct Ilib_Variable_Float with price of the variation in given currency
     */
    public function getPriceInCurrency($currency, $exchange_rate_id = 0)
    {
        return new Ilib_Variable_Float(round($this->getPrice()->getAsIso() / ($currency->getProductPriceExchangeRate((int)$exchange_rate_id)->getRate()->getAsIso() / 100) , 2));
    }

    /**
     * Returns price include vat of the product
     *
     * @return object Ilib_Variable_Float with price including vat
     */
    public function getPriceIncludingVat()
    {
        return new Ilib_Variable_Float($this->getPrice()->getAsIso(2) * (1 + $this->getVatPercent()/100));
    }

    /**
     * returns the price including vat in given currency
     *
     * @param object $currency Intraface_modules_currency_Currency
     * @param integer $exchange_rate_id
     * @return object Ilib_Variable_Float with price including vat in given currency
     */
    public function getPriceIncludingVatInCurrency($currency, $exchange_rate_id)
    {
        return new Ilib_Variable_Float($this->getPriceIncludingVat()->getAsIso() / ($currency->getProductPriceExchangeRate((int)$exchange_rate_id)->getRate()->getAsIso() / 100));
    }
    
    /**
     * Returns the before price of the product
     *
     * @return object Ilib_Variable_Float with price
     */
    public function getBeforePrice()
    {
        if(is_object($this->before_price)) {
            return $this->before_price;
        }
        return new Ilib_Variable_Float($this->before_price);
    }

    /**
     * Returns the before price of the product in given currency
     *
     * @param object $currency Intraface_modules_currency_Currency
     * @param integer $exchange_rage_id
     *
     * @return obejct Ilib_Variable_Float with price of the variation in given currency
     */
    public function getBeforePriceInCurrency($currency, $exchange_rate_id = 0)
    {
        return new Ilib_Variable_Float(round($this->getBeforePrice($product)->getAsIso() / ($currency->getProductPriceExchangeRate((int)$exchange_rate_id)->getRate()->getAsIso() / 100) , 2));
    }

    /**
     * Returns before price include vat of the product
     *
     * @return object Ilib_Variable_Float with price including vat
     */
    public function getBeforePriceIncludingVat()
    {
        return new Ilib_Variable_Float($this->getBeforePrice()->getAsIso(2) * (1 + $this->getVatPercent()/100));
    }

    /**
     * returns the before price including vat in given currency
     *
     * @param object $currency Intraface_modules_currency_Currency
     * @param integer $exchange_rate_id
     * @return object Ilib_Variable_Float with price including vat in given currency
     */
    public function getBeforePriceIncludingVatInCurrency($currency, $exchange_rate_id)
    {
        return new Ilib_Variable_Float($this->getBeforePriceIncludingVat()->getAsIso() / ($currency->getProductPriceExchangeRate((int)$exchange_rate_id)->getRate()->getAsIso() / 100));
    }

    /**
     * Returns the weight of the product
     *
     * @return object Ilib_Variable_Float with price
     */
    public function getWeight()
    {
        if(is_object($this->weight)) {
            return $this->weight;
        }
        return new Ilib_Variable_Float($this->weight);
    }
    
    /**
     * Gets the corresponding unit to a key
     *
     * @param string $key The unit key
     *
     * @return array
     */
    public static function getUnits($key = null)
    {
        $units = array(
            1 => array('singular' => '',
                      'plural' => '',
                      'combined' => ''),
            2 => array('singular' => 'unit',
                      'plural' => 'units',
                      'combined' => 'unit(s)'),
            3 => array('singular' => 'day',
                      'plural' => 'days',
                      'combined' => 'day(s)'),
            4 => array('singular' => 'month (singular)',
                      'plural' => 'month (plural)',
                      'combined' => 'month (combined)'),
            5 => array('singular' => 'year',
                      'plural' => 'years',
                      'combined' => 'year(s)'),
            6 => array('singular' => 'hour',
                      'plural' => 'hours',
                      'combined' => 'hour(s)')
        );

        if ($key === null) {
            return $units;
        } else {
            if (!empty($units[$key])) {
                return $units[$key];
            } else {
                return '';
            }
        }
    }
    
    /**
     * Returns the unit of the product
     * 
     * @return array with unit in different inflection
     */
    public function getUnit()
    {
        return Intraface_modules_product_Product_Details::getUnits($this->unit);
    }
    
    /**
     * Returns the var percenttage of the product
     * 
     * @return object Ilib_Variable_Float with vat percent
     */
    public function getVatPercent()
    {
        if($this->vat == 1) {
            return new Ilib_Variable_Float(25);
        }
        else {
            return new Ilib_Variable_Float(0);
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
     * Returns the account id which the product is going to be stated
     * 
     * @return integer account id
     */
    public function getStateAccountId()
    {
        return $this->state_account_id;
    }
    
    /**
     * Saves the acount id only;
     * @param integer $id
     * @return boolean true on succes or throws exception
     */
    public function setStateAccountId($id) 
    {
        $this->state_account_id = $id;
        $this->save();
        
        return true;
    }

}