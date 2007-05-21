<?php
/**
 * Basket
 *
 * PHP version 5
 *
 * @package Webshop
 * @author Lars Olesen <lars@legestue.net>
 */

require_once 'MDB2.php';
require_once 'Intraface/3Party/Database/Db_sql.php';
require_once 'Intraface/functions/functions.php';
require_once 'Intraface/modules/product/Product.php';

class Basket
{

    /**
     * Webshop
     * @var object
     * @access public
     */
    var $webshop;

    /**
     * Session_id
     * Variablen bruges, fordi webshop almindeligvis bruges uden for systemet.
     * For at kunne holde fx indkøbskurven intakt, så skal den altså kunne fastholde
     * session id'et. Det ville den ikke kunne, fordi hver kontakt over xml-rpc jo
     * er en ny forespørgsel og altså en ny session på serveren.
     *
     * @var varchar
     * @access public
     */
    var $session_id;

    /**
     * Sql_extra
     * Måske unødvendig, da den efter ændringer i klassen altid er konstant. Men fordi
     * det var lettere at bibeholde den, blev det sådan.
     * @var varchar
     * @access public
     */
    var $sql_extra; // bruges så vi ikke behøver at tjekke om der skal skelnes på session eller id

    /**
     * Constructor
     *
     * Konstruktøren sørger også for at rydde op i Kurven.
     *
     * @param object $webshop    The webshop object
     * @param string $session_id A session id
     */
    function __construct($webshop, $session_id)
    {
        if (!is_object($webshop) AND strtolower(get_class($webshop)) == 'webshop') {
            trigger_error('Basket kræver objektet Webshop', E_USER_ERROR);
        }

        $session_id = safeToDb($session_id);

        if (empty($session_id)) {
            trigger_error('basket needs that session id', E_USER_ERROR);
        }

        $this->webshop = $webshop;
        $this->sql_extra = " session_id = '" . $session_id . "'";

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
    function add($product_id, $quantity = 1)
    {
        $product_id = intval($product_id);
        $quantity = intval($quantity);
        $quantity = $this->getItemCount($product_id) + $quantity;
        return $this->change($product_id, $quantity);
    }

    /**
     * Removes a product from the basket
     *
     * @param integer $product_id Product to remove
     * @param integer $quantity   How many should be removed
     *
     * @return boelean
     */
    function remove($product_id, $quantity = 1)
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
    function change($product_id, $quantity, $text = '', $basketevaluation = 0)
    {
        $db = new DB_Sql;
        $product_id = (int)$product_id;
        $quantity = (int)$quantity;

        $this->webshop->kernel->useModule('product');
        $product = new Product($this->webshop->kernel, $product_id);

        if (is_object($product->stock) AND $product->stock->get('for_sale') < $quantity AND $quantity > 0) {
            return false;
        }

        $db->query("SELECT id, quantity FROM basket WHERE product_id = $product_id
                AND basketevaluation_product = " . $basketevaluation . "
                AND " . $this->sql_extra. "
                AND intranet_id = " . $this->webshop->kernel->intranet->get('id'));

        if ($db->nextRecord()) {
            if ($quantity <= 0) {
                $db->query("DELETE FROM basket
                    WHERE id = ".$db->f('id') . "
                        AND basketevaluation_product = " . $basketevaluation . "
                        AND " . $this->sql_extra . "
                        AND intranet_id = " . $this->webshop->kernel->intranet->get("id"));
            } else {
                $db->query("UPDATE basket SET quantity = $quantity, date_changed = NOW()
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
                        basketevaluation_product = " . $basketevaluation . ",
                        product_id = $product_id,
                        intranet_id = " . $this->webshop->kernel->intranet->get('id') . ",
                        " . $this->sql_extra);
            return true;
        }

    }

    /**
     * Counts the number of a certain product in the basket
     *
     * @param integer $product_id Product id of the product to count
     *
     * @return integer
     */
    function getItemCount($product_id)
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
    function getTotalPrice()
    {
        $price = 0;

        $db = new DB_Sql;
        $db->query("SELECT product_id, quantity FROM basket WHERE " . $this->sql_extra);

        while ($db->nextRecord()) {
            $product = new Product($this->webshop->kernel, $db->f("product_id"));
            $price += $product->get('price_incl_vat') * $db->f("quantity");

        }

        return $price;

    }

    /**
     * Gets the total weight of the basket
     *
     * @return float
     */
    function getTotalWeight()
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
    function getItems()
    {
        $items = array();
        $db = new DB_Sql;

        $db->query("SELECT
                product.id,
                basket.product_id,
                product_detail.name,
                product_detail.price,
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

        $i = 0;
        while ($db->nextRecord()) {

            $items[$i]['id'] = $db->f("id");
            $product = new Product($this->webshop->kernel, $db->f("id"));
            $product->getPictures();
            $items[$i]['product_id'] = $product->get('id');
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
    function removeEvaluationProducts()
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
    function reset()
    {
        $db = new DB_Sql;
        $db->query("UPDATE basket SET session_id = '' WHERE " . $this->sql_extra . " AND intranet_id = " . $this->webshop->kernel->intranet->get("id"));
        return true;
    }
}
?>