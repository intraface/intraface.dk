<?php
class Intraface_modules_product_Variation_Detail extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('product_variation_detail');
        $this->hasColumn('date_created', 'datetime', array());
        $this->hasColumn('product_variation_id', 'integer', 11, array());
        $this->hasColumn('price_difference', 'integer', 11, array());
        $this->hasColumn('weight_difference', 'integer', 11, array());
    }

    public function setUp()
    {
        $this->actAs('Intraface_Doctrine_Template_Intranet');
        // This relation is skippend because of several variation classes
        // $this->hasOne('Intraface_modules_product_Variation as variation', array('local' => 'product_variation_id', 'foreign' => 'id'));
    }

    public function preInsert()
    {
        $this->date_created = date('Y-m-d H:i:s');
    }

    public function preUpdate($event)
    {
        $new = new Intraface_modules_product_Variation_Detail;
        $new->product_variation_id = $this->product_variation_id;
        $new->price_difference = $this->price_difference;
        $new->weight_difference = $this->weight_difference;
        $new->save();

        $event->skipOperation();
    }

    /**
     * Returns the id of the detail
     *
     * @return integer id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the price difference on the variation
     *
     * @return float price difference
     */
    public function getPriceDifference()
    {
        return $this->price_difference;
    }

    /**
     * returns weight differnece in grams
     *
     * @return integer weight difference in grams
     */
    public function getWeightDifference()
    {
        return $this->weight_difference;
    }

    function getIntranetId()
    {
    	return $this->intranet_id;
    }

    /**
     * Returns the price of the variation
     *
     * @todo Product should not be given as parameter, but defined as relation. Product needs to be made in doctrine
     *
     * @param object Product
     *
     * @return object Ilib_Variable_Float with price
     */
    public function getPrice($product)
    {
        return new Ilib_Variable_Float(round($product->get('price') + $this->getPriceDifference(), 2));
    }

    /**
     * Returns the price of the variation in given currency
     *
     * @todo Product should not be given as parameter, but defined as relation. Product needs to be made in doctrine
     *
     * @param object $currency Intraface_modules_currency_Currency
     * @param integer $exchange_rage_id
     * @param object $product Product
     *
     * @return obejct Ilib_Variable_Float with price of the variation in given currency
     */
    public function getPriceInCurrency($currency, $exchange_rate_id = 0, $product)
    {
        return new Ilib_Variable_Float(round($this->getPrice($product)->getAsIso() / ($currency->getProductPriceExchangeRate((int)$exchange_rate_id)->getRate()->getAsIso() / 100) , 2));
    }

    /**
     * Returns price include vat of the variation
     *
     * @todo Product should not be given as parameter, but defined as relation. Product needs to be made in doctrine
     *
     * @param object $product Product
     *
     * @return object Ilib_Variable_Float with price including vat
     */
    public function getPriceIncludingVat($product)
    {
        return new Ilib_Variable_Float($this->getPrice($product)->getAsIso(2) * (1 + $product->get('vat_percent')/100));
    }

    /**
     * returns the price including vat in given currency
     *
     * @todo Product should not be given as parameter, but defined as relation. Product needs to be made in doctrine
     *
     * @param object $currency Intraface_modules_currency_Currency
     * @param integer $exchange_rate_id
     * @param object product Product
     *
     * @return object Ilib_Variable_Float with price including vat in given currency
     */
    public function getPriceIncludingVatInCurrency($currency, $exchange_rate_id, $product)
    {
        return new Ilib_Variable_Float($this->getPriceIncludingVat($product)->getAsIso() / ($currency->getProductPriceExchangeRate((int)$exchange_rate_id)->getRate()->getAsIso() / 100));
    }

    /**
     * Returns the weight of the variation
     *
     * @todo Product should not be given as parameter, but defined as relation. Product needs to be made in doctrine
     *
     * @param object Product
     *
     * @return object Ilib_Variable_Float with price
     */
    public function getWeight($product)
    {
        return new Ilib_Variable_Float(round($product->get('weight') + $this->getWeightDifference(), 0));
    }
}