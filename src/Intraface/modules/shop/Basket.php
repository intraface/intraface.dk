<?php
/**
 * Basket
 *
 * PHP version 5
 *
 * @package Intraface_Shop
 * @author Lars Olesen <lars@legestue.net>
 */
require_once 'Intraface/functions.php';
require_once 'Intraface/modules/product/Product.php';

class Intraface_modules_shop_Basket
{
    /**
     * @var object
     */
    private $webshop;

    /**
     * @var object
     */
    private $coordinator;

    /**
     * @var object
     */
    private $intranet;

    /**
     * Variablen bruges, fordi webshop almindeligvis bruges uden for systemet.
     * For at kunne holde fx indkøbskurven intakt, så skal den altså kunne fastholde
     * session id'et. Det ville den ikke kunne, fordi hver kontakt over xml-rpc jo
     * er en ny forespørgsel og altså en ny session på serveren.
     *
     * @var string
     */
    private $session_id;

    /**
     * @var object
     */
    private $db;

    /**
     * @var array $items
     */
    private $items;

    const CLEAN_UP_AFTER = 2;

    /**
     * Constructor
     *
     * @param object $db         The webshop object
     * @param object $intranet   The webshop object
     * @param object $webshop    The webshop object
     * @param string $session_id A session id
     *
     * @return void
     */
    public function __construct($db, $intranet, $coordinator, $shop, $session_id)
    {
        $this->db          = $db;
        $this->intranet    = $intranet;
        $this->coordinator = $coordinator;
        $this->webshop     = $shop;
        $this->session_id  = $session_id;
        $this->resetItemCache();

        $this->conditions = array('session_id = ' . $this->db->quote($this->session_id, 'text'),
                                  'shop_id = ' . $this->db->quote($this->webshop->getId(), 'integer'),
                                  'intranet_id = ' . $this->db->quote($this->intranet->getId(), 'integer'));

        $this->cleanUp();
    }

    private function cleanUp()
    {
        return $this->db->query("DELETE FROM basket WHERE DATE_ADD(date_changed, INTERVAL " . $this->db->quote(self::CLEAN_UP_AFTER, 'integer') . " HOUR) < NOW()");
    }

    /**
     * Adds a product to the basket
     *
     * @param integer $product_id Id of product
     * @param integer $quantity   Number of products to add
     *
     * @return boolean
     */
    public function add($product_id, $product_variation_id = 0, $quantity = 1, $text = '', $product_detail_id = 0)
    {
        $product_id = intval($product_id);
        $product_variation_id = intval($product_variation_id);
        $quantity = intval($quantity);
        $quantity = $this->getItemCount($product_id, $product_variation_id) + $quantity;
        return $this->change($product_id, $product_variation_id, $quantity, $text, $product_detail_id);
    }

    /**
     * Removes a product from the basket
     *
     * @param integer $product_id Product to remove
     * @param integer $quantity   How many should be removed
     *
     * @return boelean
     */
    public function remove($product_id, $product_variation_id = 0, $quantity = 1)
    {
        $product_id = intval($product_id);
        $product_variation_id = intval($product_variation_id);
        $quantity = intval($quantity);
        $quantity = $this->getItemCount($product_id, $product_variation_id) - $quantity;
        return $this->change($product_id, $product_variation_id, $quantity);
    }

    /**
     * Changes a product in the basket
     *
     * @param integer $product_id       Product id
     * @param integer $quantity         The quantity
     * @param string  $text	            To add description to product, not yet implemented
     * @param integer $basketevaluation Wheter the product is from basketevaluation
     *
     * @return boolean
     */
    public function change($product_id, $product_variation_id, $quantity, $text = '', $product_detail_id = 0, $basketevaluation = 0)
    {
        $db = new DB_Sql;
        $product_id = (int)$product_id;
        $product_variation_id = intval($product_variation_id);
        $product_detail_id = (int)$product_detail_id;
        $quantity = (int)$quantity;

        $this->resetItemCache();

        $this->coordinator->kernel->useModule('product');

        $product = new Product($this->coordinator->kernel, $product_id, $product_detail_id);

        if ($product->get('id') == 0) {
            return false;
        }

        if ($product->get('stock')) {
            if ($product_variation_id != 0) {
                $variation = $product->getVariation($product_variation_id);
                if (!$variation->getId()) {
                    return false;
                }
                $stock = $variation->getStock($product);
            } else {
                $stock = $product->getStock();
            }

            if (is_object($stock) AND $stock->get('for_sale') < $quantity AND $quantity != 0) {
                return false;
            }
        }

        $sql_extra = implode(" AND ", $this->conditions);

        $db->query("SELECT id, quantity
                FROM basket
                WHERE product_id = ".$product_id."
                    AND product_variation_id = ".$product_variation_id."
                    AND product_detail_id = " . $product_detail_id . "
                    AND basketevaluation_product = " . $basketevaluation . "
                    AND " . $sql_extra);

        if ($db->nextRecord()) {
            if ($quantity == 0) {
                $db->query("DELETE FROM basket
                    WHERE id = ".$db->f('id') . "
                        AND basketevaluation_product = " . $basketevaluation . "
                        AND " . $sql_extra);
            } else {
                $db->query("UPDATE basket SET
                    quantity = $quantity,
                    date_changed = NOW(),
                    text = '".$text."'
                    WHERE id = ".$db->f('id') . "
                        AND basketevaluation_product = " . $basketevaluation . "
                        AND " . $sql_extra);
            }
            return true;
        } else {
            $sql_extra = implode(', ', $this->conditions);
            $db->query("INSERT INTO basket
                    SET
                        quantity = $quantity,
                        date_changed = NOW(),
                        text = '".$text."',
                        basketevaluation_product = " . $basketevaluation . ",
                        product_id = ".$product_id.",
                        product_variation_id = ".$product_variation_id.",
                        product_detail_id = ".$product_detail_id.",
                        " . $sql_extra);
            return true;
        }
    }

    /**
     * Save order details
     *
     * @param (array)input	array with buyer details
     *
     * @return boolean true or false
     */
    public function saveAddress($input)
    {
        settype($input['name'], 'string');
        settype($input['contactperson'], 'string');
        settype($input['address'], 'string');
        settype($input['postcode'], 'string');
        settype($input['city'], 'string');
        settype($input['country'], 'string');
        settype($input['cvr'], 'string');
        settype($input['email'], 'string');
        settype($input['phone'], 'string');

        $sql = "name = \"".safeToDb($input['name'])."\"," .
            "contactperson = \"".safeToDb($input['contactperson'])."\", " .
            "address = \"".safeToDb($input['address'])."\", " .
            "postcode = \"".safeToDb($input['postcode'])."\", " .
            "city = \"".safeToDb($input['city'])."\", ".
            "country = \"".safeToDb($input['country'])."\", ".
            "cvr = \"".safeToDb($input['cvr'])."\", ".
            "email =\"".safeToDb($input['email'])."\", ".
            "phone = \"".safeToDb($input['phone'])."\"";

        return $this->saveToDb($sql);
    }

    /**
     * Save customer coupon
     *
     * @param string $customer_coupon customer coupon
     *
     * @return boolean true or false
     */
    public function saveCustomerCoupon($customer_coupon)
    {
        $sql = "customer_coupon = \"".$customer_coupon."\"";

        return $this->saveToDb($sql);
    }

    /**
     * Save customer EAN location number
     *
     * @param string $customer_ean customer coupon
     *
     * @return boolean true or false
     */
    public function saveCustomerEan($customer_ean)
    {
        $sql = "customer_ean = \"".$customer_ean."\"";

        return $this->saveToDb($sql);
    }

    /**
     * Save customer comment
     *
     * @param string $customer_comment comment
     *
     * @return boolean true or false
     */
    public function saveCustomerComment($customer_comment)
    {
        $sql = "customer_comment = \"".$customer_comment."\"";

        return $this->saveToDb($sql);

    }

    /**
     * Save Payment method
     *
     * @param string $payment_method
     *
     * @return boolean true or false
     */
    public function savePaymentMethod($payment_method)
    {

        $payment_method_key = $this->coordinator->getPaymentMethodKeyFromIdenfifier($payment_method);

        $sql = "payment_method_key = \"".$payment_method_key."\"";

        return $this->saveToDb($sql);

    }

    /**
     * @todo Is this really public
     * @todo Strange name
     *
     * @param string $sql Extra sql string to add
     */
    public function saveToDb($sql)
    {
        $sql_extra = implode(" AND ", $this->conditions);

        $db = new DB_Sql;
        $db->query("SELECT id FROM basket_details WHERE " . $sql_extra. "
                AND intranet_id = " . $this->intranet->getId());
        if ($db->nextRecord()) {
            $db->query("UPDATE basket_details SET ".$sql.",
                date_changed = NOW()
                WHERE id = ".$db->f('id') . "
                    AND " . $sql_extra . "
                    AND intranet_id = " . $this->intranet->getId());
            return true;
        } else {
            $sql_extra = implode(", ", $this->conditions);
            $db->query("INSERT INTO basket_details
                    SET ".$sql.",
                        date_changed = NOW(),
                        date_created = NOW(),
                        " . $sql_extra);

            return true;
        }
    }

    /**
     * Return buyer details
     *
     * @todo Should return an object
     *
     * @return array of buyer details.
     */
    public function getAddress()
    {
        $sql_extra = implode(" AND ", $this->conditions);
        $db = new DB_Sql;
        $db->query("SELECT *
            FROM basket_details
            WHERE " . $sql_extra . "
                AND intranet_id = " . $this->intranet->getId());
        if (!$db->nextRecord()) {
            return array();
        }

        return array('name' => $db->f('name'),
            'contactperson' => $db->f('contactperson'),
            'address' => $db->f('address'),
            'postcode' => $db->f('postcode'),
            'city' => $db->f('city'),
            'country' => $db->f('country'),
            'cvr' => $db->f('cvr'),
            'email' => $db->f('email'),
            'phone' => $db->f('phone'));
    }

    /**
     * Return customer coupon
     *
     * @todo Why return an array
     *
     * @return array with customer coupon
     */
    public function getCustomerCoupon()
    {
        $sql_extra = implode(" AND ", $this->conditions);
        $db = new DB_Sql;
        $db->query("SELECT customer_coupon
            FROM basket_details
            WHERE " . $sql_extra . "
                AND intranet_id = " . $this->intranet->getId());
        if (!$db->nextRecord()) {
            return array();
        }

        return array('customer_coupon' => $db->f('customer_coupon'));
    }

    /**
     * Return customer EAN location number
     *
     * @todo Why return an array
     *
     * @return array with customer ean
     */
    public function getCustomerEan()
    {
        $sql_extra = implode(" AND ", $this->conditions);
        $db = new DB_Sql;
        $db->query("SELECT customer_ean
            FROM basket_details
            WHERE " . $sql_extra . "
                AND intranet_id = " . $this->intranet->getId());
        if (!$db->nextRecord()) {
            return array();
        }

        return array('customer_ean' => $db->f('customer_ean'));
    }

    /**
     * Return customer coupon
     *
     * @todo Why return an array
     *
     * @return array with customer coupon
     */
    public function getCustomerComment()
    {
        $sql_extra = implode(" AND ", $this->conditions);
        $db = new DB_Sql;
        $db->query("SELECT customer_comment
            FROM basket_details
            WHERE " . $sql_extra . "
                AND intranet_id = " . $this->intranet->getId());
        if (!$db->nextRecord()) {
            return array();
        }

        return array('customer_comment' => $db->f('customer_comment'));
    }

    /**
     * Return payment method
     *
     * @todo Why return an array? Then it is possible to extend with more data later.
     *
     * @return array with customer coupon
     */
    public function getPaymentMethod()
    {
        $sql_extra = implode(" AND ", $this->conditions);
        $db = new DB_Sql;
        $db->query("SELECT payment_method_key
            FROM basket_details
            WHERE " . $sql_extra . "
                AND intranet_id = " . $this->intranet->getId());
        if (!$db->nextRecord() || $db->f('payment_method_key') == 0) {
            return array();
        }

        return array('payment_method' => $this->coordinator->getPaymentMethodFromKey($db->f('payment_method_key')));
    }

    /**
     * Counts the number of a certain product in the basket
     *
     * @param integer $product_id Product id of the product to count
     *
     * @return integer
     */
    public function getItemCount($product_id, $product_variation_id = 0)
    {
        $product_id = (int)$product_id;
        $product_variation_id = (int)$product_variation_id;

        $sql_extra = implode(" AND ", $this->conditions);
        $db = new DB_Sql;
        $db->query("SELECT *
            FROM basket
            WHERE " . $sql_extra . "
                AND product_id = " . $product_id . "
                AND product_variation_id = ".$product_variation_id."
                AND intranet_id = " . $this->intranet->getId() . "
      AND quantity > 0 LIMIT 1");

        if (!$db->nextRecord()) {
            return 0;
        }
        return $db->f("quantity");

    }

    /**
     * Gets the total price of the basket
     *
     * @return float
     */
    public function getTotalPrice($type = 'including_vat')
    {
        $price = 0;

        foreach ($this->getItems() AS $item) {
            if ($type == 'exclusive_vat') {
                $price += $item['totalprice'];
            }
            else {
                $price += $item['totalprice_incl_vat'];
            }
        }

        return round($price, 2);
    }

    /**
     * Gets the total price of the basket in given currencies
     *
     * @param object $currencies Collection of currencies
     * @return float
     */
    public function getTotalPriceInCurrencies($currencies)
    {

        $total['DKK']['ex_vat'] = 0;
        $total['DKK']['incl_vat'] = 0;

        if (is_object($currencies) && $currencies->count() > 0) {
            foreach ($currencies AS $currency) {
                $total[$currency->getType()->getIsoCode()]['ex_vat'] = 0;
                $total[$currency->getType()->getIsoCode()]['incl_vat'] = 0;
            }
        }

        foreach ($this->getItems($currencies) AS $item) {

            $total['DKK']['ex_vat'] += $item['currency']['DKK']['price'];
            $total['DKK']['incl_vat'] += $item['currency']['DKK']['totalprice_incl_vat'];

            if (is_object($currencies) && $currencies->count() > 0) {
                foreach ($currencies AS $currency) {
                    $total[$currency->getType()->getIsoCode()]['ex_vat'] += $item['currency'][$currency->getType()->getIsoCode()]['price'];
                    $total[$currency->getType()->getIsoCode()]['incl_vat'] += $item['currency'][$currency->getType()->getIsoCode()]['totalprice_incl_vat'];
                }
            }
        }

        return $total;
    }

    /**
     * Gets the total weight of the basket
     *
     * @return float
     */
    public function getTotalWeight()
    {
        $weight = 0;

        foreach ($this->getItems() AS $item) {
            $weight += $item['totalweight'];
        }

        return $weight;
    }

    /**
     * Gets all items in the basket
     *
     * @return array
     */
    public function getItems($currencies = false)
    {

        // Local cache
        if ($this->items !== NULL && is_array($this->items)) {
            return $this->items;
        }

        $sql_extra = implode(" AND basket.", $this->conditions);
        $items = array();
        $db = new DB_Sql;

        $db->query("SELECT
                product.id,
                basket.product_id,
                basket.product_variation_id,
                basket.product_detail_id,
                product_detail.name,
                product_detail.price,
                basket.quantity,
                basket.text,
                basket.basketevaluation_product
            FROM basket
            INNER JOIN product
                ON product.id = basket.product_id
            INNER JOIN product_detail
                ON product.id = product_detail.product_id
            WHERE " . $sql_extra . "
                AND product_detail.active = 1
                AND basket.intranet_id = " . $this->intranet->getId() . "
            ORDER BY product_detail.vat DESC, basket.basketevaluation_product");

        $i = 0;
        while ($db->nextRecord()) {

            $items[$i]['id'] = $db->f("id");
            $items[$i]['text'] = $db->f("text");
            $items[$i]['basketevaluation_product'] = $db->f("basketevaluation_product");
            $product = new Product($this->coordinator->kernel, $db->f("id"));
            $product->getPictures();
            $items[$i]['product_id'] = $product->get('id');
            $items[$i]['product_detail_id'] = $product->get('product_detail_id');
            $items[$i]['pictures'] = $product->get('pictures');

            //if ($db->f('product_variation_id') != 0) {
            if ($product->hasVariation()) {
                $variation = $product->getVariation($db->f('product_variation_id'));
                $detail = $variation->getDetail();
                $items[$i]['product_variation_id'] = $variation->getId();
                $items[$i]['name'] = $product->get('name').' - '.$variation->getName();
                $items[$i]['weight'] = $detail->getWeight($product)->getAsIso(0);
                $items[$i]['price'] = $detail->getPrice($product)->getAsIso(2);
                $items[$i]['price_incl_vat'] = $detail->getPriceIncludingVat($product)->getAsIso(2);

                $items[$i]['currency']['DKK']['price'] = $detail->getPrice($product)->getAsIso();
                $items[$i]['currency']['DKK']['price_incl_vat'] = $detail->getPriceIncludingVat($product)->getAsIso(4);
                if (is_object($currencies) && $currencies->count() > 0) {
                    foreach ($currencies AS $currency) {
                        $items[$i]['currency'][$currency->getType()->getIsoCode()]['price'] = $detail->getPriceInCurrency($currency, 0, $product)->getAsIso();
                        $items[$i]['currency'][$currency->getType()->getIsoCode()]['price_incl_vat'] = $detail->getPriceIncludingVatInCurrency($currency, 0, $product)->getAsIso(4);
                    }
                }
            } else {
                $items[$i]['product_variation_id'] = 0;
                $items[$i]['name'] = $product->get('name');
                $items[$i]['weight'] = $product->get('weight');
                $items[$i]['price'] = $product->getDetails()->getPrice()->getAsIso(2);
                $items[$i]['price_incl_vat'] = $product->getDetails()->getPriceIncludingVat()->getAsIso(2);

                $items[$i]['currency']['DKK']['price'] = $product->getDetails()->getPrice()->getAsIso(2);
                $items[$i]['currency']['DKK']['price_incl_vat'] = $product->getDetails()->getPriceIncludingVat()->getAsIso(2);
                if (is_object($currencies) && $currencies->count() > 0) {
                    foreach ($currencies AS $currency) {
                        $items[$i]['currency'][$currency->getType()->getIsoCode()]['price'] = $product->getDetails()->getPriceInCurrency($currency)->getAsIso(2);
                        $items[$i]['currency'][$currency->getType()->getIsoCode()]['price_incl_vat'] = $product->getDetails()->getPriceIncludingVatInCurrency($currency)->getAsIso(2);
                    }
                }
            }

            // basket specific
            $items[$i]['quantity'] = $db->f('quantity');
            $items[$i]['totalweight'] = $items[$i]['weight'] * $db->f('quantity');
            $items[$i]['totalprice'] = $db->f('quantity') * $items[$i]['price'];
            $items[$i]['totalprice_incl_vat'] = $db->f('quantity') * $items[$i]['price_incl_vat'];

            $items[$i]['currency']['DKK']['totalprice'] = $db->f('quantity') * $items[$i]['currency']['DKK']['price'];
            $items[$i]['currency']['DKK']['totalprice_incl_vat'] = $db->f('quantity') * $items[$i]['currency']['DKK']['price_incl_vat'];
            if (is_object($currencies) && $currencies->count() > 0) {
                foreach ($currencies AS $currency) {
                    $items[$i]['currency'][$currency->getType()->getIsoCode()]['totalprice'] = $db->f('quantity') * $items[$i]['currency'][$currency->getType()->getIsoCode()]['price'];
                    $items[$i]['currency'][$currency->getType()->getIsoCode()]['totalprice_incl_vat'] = $db->f('quantity') * $items[$i]['currency'][$currency->getType()->getIsoCode()]['price_incl_vat'];
                }
            }

            $i++;
        }

        $this->items = $items;
        return $items;
    }

    /**
     * Resets the item cache
     */
    private function resetItemCache()
    {
        $this->items = NULL;
    }

    /**
     * Helperfunction for BasketEvaluation. Removes all products from basket
     * placed by BasketEvaluation.
     *
     * @return boolean
     */
    public function removeEvaluationProducts()
    {
        $sql_extra = implode(" AND ", $this->conditions);
        $db = new DB_Sql;
        $db->query("DELETE FROM basket " .
                "WHERE basketevaluation_product = 1 " .
                    "AND " . $sql_extra . " " .
                    "AND intranet_id = " . $this->intranet->getId());

        $this->resetItemCache();

        return true;

    }

    /**
     * Resets the basket for a session
     *
     * @return boolean
     */
    public function reset()
    {
        $this->resetItemCache();

        $sql_extra = implode(" AND ", $this->conditions);
        $db = new DB_Sql;
        $db->query("UPDATE basket SET session_id = '' WHERE " . $sql_extra . " AND intranet_id = " . $this->intranet->getId());
        $db->query("UPDATE basket_details SET session_id = '' WHERE " . $sql_extra . " AND intranet_id = " . $this->intranet->getId());

        return true;
    }
}