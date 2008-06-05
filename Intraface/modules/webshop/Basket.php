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

class Basket
{
    /**
     * @var object
     */
    public $webshop;

    /**
     * Variablen bruges, fordi webshop almindeligvis bruges uden for systemet.
     * For at kunne holde fx indkøbskurven intakt, så skal den altså kunne fastholde
     * session id'et. Det ville den ikke kunne, fordi hver kontakt over xml-rpc jo
     * er en ny forespørgsel og altså en ny session på serveren.
     *
     * @var string
     */
    public $session_id;

    /**
     * Måske unødvendig, da den efter ændringer i klassen altid er konstant. Men fordi
     * det var lettere at bibeholde den, blev det sådan.
     *
     * @var string
     */
    public $sql_extra; // bruges så vi ikke behøver at tjekke om der skal skelnes på session eller id

    /**
     * Constructor
     *
     * Konstruktøren sørger også for at rydde op i Kurven.
     *
     * @param object $webshop    The webshop object
     * @param string $session_id A session id
     *
     * @return void
     */
    public function __construct($webshop, $session_id)
    {
        if (!is_object($webshop) AND strtolower(get_class($webshop)) == 'webshop') {
            trigger_error('Basket kræver objektet Webshop', E_USER_ERROR);
        }

        if (empty($session_id)) {
            trigger_error('basket needs that session id', E_USER_ERROR);
        }

        $this->webshop = $webshop;
        $this->sql_extra = " session_id = '" . safeToDb($session_id) . "'";

        // rydder op i databasen efter fx to timer
        $clean_up_after = 2; // timer

        $db = new DB_Sql;
        $db->query("DELETE FROM basket WHERE DATE_ADD(date_changed, INTERVAL " . $clean_up_after . " HOUR) < NOW()");
    }

    /**
     * Adds a product to the basket
     *
     * @param integer $product_id Id of product
     * @param integer $quantity   Number of products to add
     *
     * @return boolean
     */
    public function add($product_id, $quantity = 1, $text = '', $product_detail_id = 0)
    {
        $product_id = intval($product_id);
        $quantity = intval($quantity);
        $quantity = $this->getItemCount($product_id) + $quantity;
        return $this->change($product_id, $quantity, $text, $product_detail_id);
    }

    /**
     * Removes a product from the basket
     *
     * @param integer $product_id Product to remove
     * @param integer $quantity   How many should be removed
     *
     * @return boelean
     */
    public function remove($product_id, $quantity = 1)
    {
        $product_id = intval($product_id);
        $quantity = intval($quantity);
        $quantity = $this->getItemCount($product_id) - $quantity;
        return $this->change($product_id, $quantity);
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
    public function change($product_id, $quantity, $text = '', $product_detail_id = 0, $basketevaluation = 0)
    {
        $db = new DB_Sql;
        $product_id = (int)$product_id;
        $product_detail_id = (int)$product_detail_id;
        $quantity = (int)$quantity;

        $this->webshop->kernel->useModule('product');

        $product = new Product($this->webshop->kernel, $product_id, $product_detail_id);


        if($product->get('id') == 0) {
            return false;
        }

        if (is_object($product->stock) AND $product->stock->get('for_sale') < $quantity AND $quantity != 0) {
            return false;
        }

        $db->query("SELECT id, quantity FROM basket WHERE product_id = $product_id
                AND product_detail_id = " . $product_detail_id . "
                AND basketevaluation_product = " . $basketevaluation . "
                AND " . $this->sql_extra. "
                AND intranet_id = " . $this->webshop->kernel->intranet->get('id'));

        if ($db->nextRecord()) {
            if ($quantity == 0) {
                $db->query("DELETE FROM basket
                    WHERE id = ".$db->f('id') . "
                        AND basketevaluation_product = " . $basketevaluation . "
                        AND " . $this->sql_extra . "
                        AND intranet_id = " . $this->webshop->kernel->intranet->get("id"));
            } else {
                $db->query("UPDATE basket SET
                    quantity = $quantity,
                    date_changed = NOW(),
                    text = '".$text."'
                    WHERE id = ".$db->f('id') . "
                        AND basketevaluation_product = " . $basketevaluation . "

                        AND " . $this->sql_extra . "
                        AND intranet_id = " . $this->webshop->kernel->intranet->get('id'));
            }
            return true;
        } else {
            $db->query("INSERT INTO basket
                    SET
                        quantity = $quantity,
                        date_changed = NOW(),
                        text = '".$text."',
                        basketevaluation_product = " . $basketevaluation . ",
                        product_id = $product_id,
                        product_detail_id = ".$product_detail_id.",
                        intranet_id = " . $this->webshop->kernel->intranet->get('id') . ",
                        " . $this->sql_extra);
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
     * @todo Is this really public
     * @todo Strange name
     *
     * @param string $sql Extra sql string to add
     */
    public function saveToDb($sql) {

        $db = new DB_Sql;
        $db->query("SELECT id FROM basket_details WHERE " . $this->sql_extra. "
                AND intranet_id = " . $this->webshop->kernel->intranet->get('id'));
        if ($db->nextRecord()) {
            $db->query("UPDATE basket_details SET ".$sql.",
                date_changed = NOW()
                WHERE id = ".$db->f('id') . "
                    AND " . $this->sql_extra . "
                    AND intranet_id = " . $this->webshop->kernel->intranet->get('id'));
            return true;
        } else {
            $db->query("INSERT INTO basket_details
                    SET ".$sql.",
                        date_changed = NOW(),
                           date_created = NOW(),
                        intranet_id = " . $this->webshop->kernel->intranet->get('id') . ",
                        " . $this->sql_extra);

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
        $db = new DB_Sql;
        $db->query("SELECT *
            FROM basket_details
            WHERE " . $this->sql_extra . "
                AND intranet_id = " . $this->webshop->kernel->intranet->get('id'));
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
        $db = new DB_Sql;
        $db->query("SELECT customer_coupon
            FROM basket_details
            WHERE " . $this->sql_extra . "
                AND intranet_id = " . $this->webshop->kernel->intranet->get('id'));
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
        $db = new DB_Sql;
        $db->query("SELECT customer_ean
            FROM basket_details
            WHERE " . $this->sql_extra . "
                AND intranet_id = " . $this->webshop->kernel->intranet->get('id'));
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
        $db = new DB_Sql;
        $db->query("SELECT customer_comment
            FROM basket_details
            WHERE " . $this->sql_extra . "
                AND intranet_id = " . $this->webshop->kernel->intranet->get('id'));
        if (!$db->nextRecord()) {
            return array();
        }

        return array('customer_comment' => $db->f('customer_comment'));
    }

    /**
     * Counts the number of a certain product in the basket
     *
     * @param integer $product_id Product id of the product to count
     *
     * @return integer
     */
    public function getItemCount($product_id)
    {
        $product_id = (int)$product_id;

        $db = new DB_Sql;
        $db->query("SELECT *
            FROM basket
            WHERE " . $this->sql_extra . "
                AND product_id = " . $product_id . "
                AND intranet_id = " . $this->webshop->kernel->intranet->get('id') . "
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

        $db = new DB_Sql;
        $db->query("SELECT product_id, quantity FROM basket WHERE " . $this->sql_extra);

        while ($db->nextRecord()) {
            $product = new Product($this->webshop->kernel, $db->f("product_id"));
            if($type == 'exclusive_vat') {
                $price += $product->get('price') * $db->f("quantity");
            } else {
                $price += $product->get('price_incl_vat') * $db->f("quantity");
            }
        }

        return $price;
    }

    /**
     * Gets the total weight of the basket
     *
     * @return float
     */
    public function getTotalWeight()
    {
        $db = new DB_Sql;

        $db->query("SELECT
                product_detail.weight,
                basket.quantity
            FROM basket
            INNER JOIN product
                ON product.id = basket.product_id
            INNER JOIN product_detail
                ON product.id = product_detail.product_id
            WHERE " . $this->sql_extra . "
                AND product_detail.active = 1
                AND basket.intranet_id = " . $this->webshop->kernel->intranet->get("id") . "
                AND basket.quantity > 0
            ");


        $weight = 0;

        while ($db->nextRecord()) {
            $weight += $db->f('weight') * $db->f('quantity');
        }

        return $weight;

    }

    /**
     * Gets all items in the basket
     *
     * @return array
     */
    public function getItems()
    {
        $items = array();
        $db = new DB_Sql;

        $db->query("SELECT
                product.id,
                basket.product_id,
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
            WHERE " . $this->sql_extra . "
                AND product_detail.active = 1
                AND basket.intranet_id = " . $this->webshop->kernel->intranet->get("id") . "
            ORDER BY product_detail.vat DESC, basket.basketevaluation_product");

        $i = 0;
        while ($db->nextRecord()) {

            $items[$i]['id'] = $db->f("id");
            $items[$i]['text'] = $db->f("text");
            $items[$i]['basketevaluation_product'] = $db->f("basketevaluation_product");
            $product = new Product($this->webshop->kernel, $db->f("id"));
            $product->getPictures();
            $items[$i]['product_id'] = $product->get('id');
            $items[$i]['product_detail_id'] = $product->get('product_detail_id');
            $items[$i]['name'] = $product->get('name');
            $items[$i]['price'] = $product->get('price');
            $items[$i]['price_incl_vat'] = $product->get('price_incl_vat');
            $items[$i]['pictures'] = $product->get('pictures');


            // basket specific
            $items[$i]['quantity'] = $db->f('quantity');
            $items[$i]['totalprice'] = $db->f('quantity') * $items[$i]['price'];
            $items[$i]['totalprice_incl_vat'] = $db->f('quantity') * $items[$i]['price_incl_vat'];

            $i++;
        }

        return $items;
    }

    /**
     * Helperfunction for BasketEvaluation. Removes all products from basket
     * placed by BasketEvaluation.
     *
     * @return boolean
     */
    public function removeEvaluationProducts()
    {
        $db = new DB_Sql;
        $db->query("DELETE FROM basket " .
                "WHERE basketevaluation_product = 1 " .
                    "AND " . $this->sql_extra . " " .
                    "AND intranet_id = " . $this->webshop->kernel->intranet->get("id"));
        return true;

    }

    /**
     * Resets the basket for a session
     *
     * @return boolean
     */
    public function reset()
    {
        $db = new DB_Sql;
        $db->query("UPDATE basket SET session_id = '' WHERE " . $this->sql_extra . " AND intranet_id = " . $this->webshop->kernel->intranet->get("id"));
        $db->query("UPDATE basket_details SET session_id = '' WHERE " . $this->sql_extra . " AND intranet_id = " . $this->webshop->kernel->intranet->get("id"));

        return true;
    }
}