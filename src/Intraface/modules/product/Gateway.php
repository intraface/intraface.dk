<?php
/**
 * Product
 *
 * Bruges til at holde styr på varerne.
 *
 * @package Intraface_Product
 * @author Lars Olesen <lars@legestue.net>
 * @see ProductDetail
 * @see Stock
 */
require_once 'Intraface/modules/product/ProductDetail.php';

class Intraface_modules_product_Gateway
{
    /**
     * @var object
     */
    private $kernel;

    /**
     * @var object
     */
    private $user;

    /**
     * @var object
     */
    private $intranet;

    /**
     * @var object
     */
    private $dbquery;

    /**
     * Constructor
     *
     * @param object  $user                Userobject
     *
     * @return void
     */
    function __construct($kernel)
    {
        $this->kernel = $kernel;
        $this->kernel->module('product');
        $this->user = $this->kernel->user;
        $this->intranet = $this->kernel->intranet;
        $this->error = new Intraface_Error;
    }

    /**
     * Creates the dbquery object

     * @return void
     */
    public function getDBQuery()
    {
        if ($this->dbquery) {
            return $this->dbquery;
        }

        $this->dbquery = new Intraface_DBQuery($this->kernel, "product", "product.active = 1 AND product.intranet_id = ".$this->intranet->getId());
        $this->dbquery->setJoin("LEFT", "product_detail detail", "detail.product_id = product.id", "detail.active = 1");
        $this->dbquery->setJoin("INNER", "product_detail_translation detail_translation", "detail.id = detail_translation.id", "detail_translation.lang = 'da'");
        
        //$this->dbquery->setFindCharacterFromField("detail.name");
        $this->dbquery->useErrorObject($this->error);
        return $this->dbquery;
    }
    
    public function setDBQuery($dbquery)
    {
        $this->dbquery = $dbquery;
    }

    /**
     * Finds a product with an id
     *
     * @param integer $id product id
     *
     * @return object
     */
    function getById($product_id, $old_product_detail_id = 0)
    {
        return new Product($this->kernel, $product_id, $old_product_detail_id);
    }

    function getFromId($product_id, $old_product_detail_id = 0)
    {
        return new Product($this->kernel, $product_id, $old_product_detail_id);
    }

    /**
     * Finds all products
     *
     * Hvis den er fra webshop bør den faktisk opsamle oplysninger om søgningen
     * så man kan se, hvad folk er interesseret i.
     * Søgemaskinen skal være tolerant for stavefejl
     *
     * @todo It is wrong to give currencies as parameter. Instead the list should
     *       be given as an object collection, and then currency should be given
     *       to the getPrice method.
     *
     * @param string $which valgfri søgeparameter - ikke aktiv endnu
     * @param object $currencies Collection of valid currencies.
     *
     * @return array indeholdende kundedata til liste
     */
    public function getAllProducts($which = 'all', $currencies = false)
    {
        switch ($this->getDBQuery()->getFilter('sorting')) {
            case 'date':
                    $this->getDBQuery()->setSorting("product.changed_date DESC");
                break;
            default:
                    $this->getDBQuery()->setSorting("detail_translation.name ASC");
                break;
        }

        if ($search = $this->getDBQuery()->getFilter("search")) {
            $this->getDBQuery()->setCondition("detail.number = '".$search."'
                OR detail_translation.name LIKE '%".$search."%'
                OR detail_translation.description LIKE '%".$search."%'");
        }
        if ($keywords = $this->getDBQuery()->getFilter("keywords")) {
            $this->getDBQuery()->setKeyword($keywords);
        }

        if ($this->getDBQuery()->checkFilter('shop_id') && $this->getDBQuery()->checkFilter('category')) {
            $category_type = new Intraface_Category_Type('shop', $this->getDBQuery()->getFilter('shop_id'));
            $this->getDBQuery()->setJoin(
                'INNER',
                'ilib_category_append',
                'ilib_category_append.object_id = product.id',
                'ilib_category_append.intranet_id = '.$this->kernel->intranet->getId());
            $this->getDBQuery()->setJoin(
                'INNER',
                'ilib_category',
                'ilib_category_append.category_id = ilib_category.id',
                'ilib_category.intranet_id = '.$this->kernel->intranet->getId(). ' ' .
                    'AND ilib_category.belong_to = '.$category_type->getBelongTo().' ' .
                    'AND ilib_category.belong_to_id = '.$category_type->getBelongToId());

            $this->getDBQuery()->setCondition('ilib_category.id = '.$this->getDBQuery()->getFilter("category"));

        }

        if ($ids = $this->getDBQuery()->getFilter("ids")) {
            if (is_array($ids) && count($ids) > 0) {
                $this->getDBQuery()->setCondition("product.id IN (".implode(', ', $ids).")");
            } else {
                $this->getDBQuery()->setCondition('1 = 0');
            }
        }

        // @todo DEN OUTPUTTER IKKE DET RIGTIGE VED KEYWORD
        switch ($which) {
            case 'webshop':
                $this->getDBQuery()->setCondition("product.do_show = 1");
                break;
            case 'stock':
                $this->getDBQuery()->setCondition("product.stock = 1");
                break;
            case 'notpublished':
                $this->getDBQuery()->setCondition("product.do_show = 0");
                break;
            case 'all': // fall through
            default:
                $sql = '';
             break;
        }

        $i        = 0; // til at give arrayet en key
        $db       = $this->getDBQuery()->getRecordset("product.id", "", false);
        $products = array();

        while ($db->nextRecord()) {
            $product = new Product($this->kernel, $db->f("id"));
            $product->getPictures();
            $products[$i] = $product->get();

            $products[$i]['currency']['DKK']['price'] = $product->getDetails()->getPrice();
            $products[$i]['currency']['DKK']['price_incl_vat'] = $product->getDetails()->getPriceIncludingVat();
            $products[$i]['currency']['DKK']['before_price'] = $product->getDetails()->getBeforePrice();
            $products[$i]['currency']['DKK']['before_price_incl_vat'] = $product->getDetails()->getBeforePriceIncludingVat();
            if ($currencies && $currencies->count() > 0) {
                foreach ($currencies as $currency) {
                    $products[$i]['currency'][$currency->getType()->getIsoCode()]['price'] = $product->getDetails()->getPriceInCurrency($currency);
                    $products[$i]['currency'][$currency->getType()->getIsoCode()]['price_incl_vat'] = $product->getDetails()->getPriceIncludingVatInCurrency($currency);
                    $products[$i]['currency'][$currency->getType()->getIsoCode()]['before_price'] = $product->getDetails()->getBeforePriceInCurrency($currency);
                    $products[$i]['currency'][$currency->getType()->getIsoCode()]['before_price_incl_vat'] = $product->getDetails()->getBeforePriceIncludingVatInCurrency($currency);
                }
            }

            if (!$product->get('has_variation') AND is_object($product->getStock()) AND strtolower(get_class($product->getStock())) == "stock") {
                $products[$i]['stock_status'] = $product->getStock()->get();
            } else {
                // alle ikke lagervarer der skal vises i webshop skal have en for_sale
                if ($product->get('stock') == 0 AND $product->get('do_show') == 1) {
                    $products[$i]['stock_status'] = array('for_sale' => 100); // kun til at stock_status
                } else {
                    $products[$i]['stock_status'] = array();
                }

            }

            // den her skal vist lige kigges igennem, for den tager jo alt med på nettet?
            // 0 = only stock
            if ($this->kernel->setting->get('intranet', 'webshop.show_online') == 0 AND $which=='webshop') { // only stock
                if (array_key_exists('for_sale', $products[$i]['stock_status']) AND $products[$i]['stock_status']['for_sale'] <= 0) {
                    continue;
                }
            }
            $i++;
        }
        $db->free();
        return $products;
    }
}
