<?php
/**
 * Basket = indkøbskurv
 *
 * Bruges af webshoppen som en indkøbskurv.
 *
 * @author Lars Olesen <lars@legestue.net>
 * @package Webshop
 *
 * @see WebshopServer.php - xmlrpc-server
 * @see Webshop
 * @see Product
 */

class Basket {

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
	 * @param $webshop (object)
	 * @param $session_id
	 * @return object
	 */

	function Basket(& $webshop, $session_id) {
		if (!is_object($webshop) AND strtolower(get_class($webshop)) == 'webshop') {
			trigger_error('Basket kræver objektet Webshop', FATAL);
		}

		$session_id = safeToDb($session_id);

		$this->webshop = & $webshop;
		$this->sql_extra = " session_id = '" . $session_id . "'";

		// rydder op i databasen efter fx to timer
		$clean_up_after = 2; // timer

		$db = new DB_Sql;
		$db->query("DELETE FROM basket WHERE DATE_ADD(date_changed, INTERVAL " . $clean_up_after . " HOUR) < NOW()");
	}

	/**
	 * add()
	 * Bruges til at tilføje varer til kurven
	 *
	 * @see change()
	 * @param product_id (int)
	 * @param quantity (int)
	 * @access public
	 * @return boelean
	 */

	function add($product_id, $quantity = 1) {
		$product_id = intval($product_id);
		$quantity = intval($quantity);
		$quantity = $this->getItemCount($product_id) + $quantity;
		return $this->change($product_id, $quantity);
	}

	/**
	 * remove()
	 * Bruges til at tilføje varer til kurven
	 *
	 * @see change()
	 * @param product_id (int)
	 * @param quantity (int)
	 * @access public
	 * @return boelean
	 */

	function remove($product_id, $quantity = 1) {
		$product_id = intval($product_id);
		$quantity = intval($quantity);
		$quantity = $this->getItemCount($product_id) - $quantity;
		return $this->change($product_id, $quantity);
	}

	/**
	 * change()
	 * Bruges til at lave ændringer i kurven.
	 *
	 * @see add() og remove()
	 * @param product_id (int)
	 * @param quantity (int)
	 * @access public
	 * @return boelean
	 */

	function change($product_id, $quantity) {
		$db = new DB_Sql;
		$product_id = (int)$product_id;
		$quantity = (int)$quantity;

	 	$this->webshop->kernel->useModule('product');
		$product = new Product($this->webshop->kernel, $product_id);


		if (is_object($product->stock) AND $product->stock->get('for_sale') < $quantity) {
			return 0;
		}

		$db->query("SELECT id, quantity FROM basket WHERE product_id = $product_id
				AND " . $this->sql_extra. "
				AND intranet_id = " . $this->webshop->kernel->intranet->get('id'));

		if($db->nextRecord()) {
			if ($quantity <= 0) {
				$db->query("DELETE FROM basket
					WHERE id = ".$db->f('id') . "
						AND " . $this->sql_extra . "
						AND intranet_id = " . $this->webshop->kernel->intranet->get("id"));
			}
			else {
				$db->query("UPDATE basket SET quantity = $quantity, date_changed = NOW()
					WHERE id = ".$db->f('id') . "
						AND " . $this->sql_extra . "
						AND intranet_id = " . $this->webshop->kernel->intranet->get('id'));
			}
			return 1;
		}
		else {
			$db->query("INSERT INTO basket
					SET
						quantity = $quantity,
						date_changed = NOW(),
						product_id = $product_id,
						intranet_id = " . $this->webshop->kernel->intranet->get('id') . ",
						" . $this->sql_extra);
			return 1;
		}

	}

	/**
	 * Tæller antallet af varer i kurven.
	 * @param product_id (int)
	 * @return integer
	 */

	function getItemCount($product_id) {
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
	 * getTotalPrice()
	 * Henter kurvens totale pris
	 *
	 * @return float
	 */

	function getTotalPrice() {
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
	 * getTotalWeight()
	 * Henter kurvens totale vægt
	 *
	 * @return float
	 */

	function getTotalWeight() {
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
	 * getItems()
	 * Henter varerne i kurven
	 *
	 * Kunne være smart om den returnerede lidt flere oplysnigner - fx billeder til produkterne
	 *
	 * return array
	 */
	function getItems() {
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
	 * reset()
   *
   * Nulstiller indkøbsvognen - det sker ganske enkelt ved at slette sessionid fra databasen.
   *
	 * @see Webshop->placeOrder();
	 * @param $field mixed
	 * @param $value mixed
	 */

	function reset() {
		$db = new DB_Sql;
		$db->query("UPDATE basket SET session_id = '' WHERE " . $this->sql_extra . " AND intranet_id = " . $this->webshop->kernel->intranet->get("id"));
		return 1;
	}

}

?>