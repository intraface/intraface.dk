<?php
/**
 * Bruges til at hente data via xml-rpc uden for selve intranettet.
 *
 * Denne klasse er en server, der gør det muligt at lave en webshop på baggrund
 * af intraface-systemet.
 *
 * Klassen <code>extends</code> en grundlæggende klasse, der har en metode
 * til at tjekke brugerens rettigheder (@see checkCredentials). 
 *
 * @author Lars Olesen <lars@legestue.net>
 */

require($_SERVER['DOCUMENT_ROOT'] . '/include_first.php');
require_once('../XmlRpcServer.php');

 
class WebshopServer extends XmlRpcServer {

	var $kernel;
	var $webshop;
	var $basket;
	var $product;
	var $credentials;

	function WebshopServer() {
		XmlRpcServer::XmlRpcServer();

		$this->addCallback(
			'products.getList',
			'this:getProducts',
			array('array', 'struct', 'string'),
			'Returns an array with all products'
		);
		$this->addCallback(
			'products.getProductsByKeywords',
			'this:getProductsByKeywords',
			array('array', 'struct', 'array'),
			'Returns an array with all products'
		); 
		 
		$this->addCallback(
			'products.getProduct',
			'this:getProduct',
			array('array', 'struct', 'integer'),
			'Returns an array with one single product'
		);
		$this->addCallback(
			'products.getRelatedProducts',
			'this:getRelatedProducts',
			array('array', 'struct', 'integer'),
			'Returns an array with all products'
		); 		
		$this->addCallback(
			'basket.add',
			'this:addBasket',
			array('boolean', 'struct', 'integer'),
			'Adds a product to the basket'
		);
		$this->addCallback(
			'basket.remove',
			'this:removeBasket',
			array('boolean', 'struct', 'integer', 'integer'),
			'Removes a product to the basket'
		);
		$this->addCallback(
			'basket.change',
			'this:changeBasket',
			array('boolean', 'struct', 'integer', 'integer'),
			'Changes a product in basket'
		);
		$this->addCallback(
			'basket.totalPrice',
			'this:getTotalPrice',
			array('float', 'struct'),
			'Returns total price'
		);
		$this->addCallback(
			'basket.totalWeight',
			'this:getTotalWeight',
			array('integer', 'struct'),
			'Returns total weight'
		);
		$this->addCallback(
			'basket.getItems',
			'this:getItems',
			array('array', 'struct'),
			'Returns items in basket'
		);
		$this->addCallback(
			'basket.placeOrder',
			'this:placeOrder',
			array('boolean', 'struct', 'integer'),
			'Places order to system'
		);

		$this->addCallback(
			'payment.addOnlinePayment',
			'this:addOnlinePayment',
			array('boolean', 'struct', 'integer', 'string', 'string', 'double'),
			'Adds onlinepayment to an order'
		);		

		$this->serve();

	}

	function factoryWebshop() {
		$this->kernel->module('webshop');
		$this->webshop = new Webshop($this->kernel, $this->credentials['session_id']);
	}
	
	/**
	 * Henter en liste med produkter
	 *
	 * @param $args
	 * - [0] $credentials
	 * - [1] optional search term
	 * @return array
	 * @access public
	 */

	function getProducts($arg) {
		
		$search = '';
		
		if (is_object($return = $this->checkCredentials($arg[0]))) {
			return $return; 
		}
		
		if (!empty($arg[1])) { 
			$search = strip_tags(safeEscapeString($arg[1])); 
		}

		
		$this->factoryWebshop();
		
		$products = array();
		
		$product = new Product($this->webshop->kernel);
		$product->dbquery->setFilter('search', $search);
		$products = $product->getList('webshop');
		
		/*
		$product->dbquery->defineCharacter("character", "detail.name");
		$product->dbquery->usePaging("paging");
		$product->dbquery->storeResult("use_stored", "products", "toplevel");
		*/
		$products = $product->getList('webshop');

		return array(
			'products' => $products,
			'paging' => $product->dbquery->getPaging()
		);		

	}
	
	/**
	 * Henter en liste med produkter ud fra Keyword
	 *
	 * @param $args
	 * - [0] $credentials
	 * - [1] optional search term
	 * @return array
	 * @access public
	 */
	function getProductsByKeywords($arg) {

		if (is_object($return = $this->checkCredentials($arg[0]))) {
			return $return; 
		}
		
		$this->factoryWebshop();		

		// her bør laves tjek på keyword_id eller tage det Product.
		$keyword_id = $arg[1];
		
		$list = array();
		
		$product = new Product($this->webshop->kernel);
		$keywords = $product->getKeywords();
		
		$i = 0;	
		foreach ($keywords->getList($keyword_id) AS $key=>$value) {
			$c = new Product($this->webshop->kernel, $value);
			if (is_object($c->stock)) {
				$list[$i]['actual_stock'] = $c->stock->get('actual_stock');
			}
			/*
			else {
				continue;
				//$list[$i]['actual_stock'] = 0;     
			}
			*/

			$list[$i]['id'] = $c->get('id');
			$list[$i]['number'] = $c->get('number');
			$list[$i]['name'] = $c->get('name');
			$list[$i]['description'] = $c->get('description');			
			$list[$i]['vat'] = $c->get('vat');		
			$list[$i]['unit'] = $c->get('unit');
			$list[$i]['weight'] = $c->get('weight');     
			$list[$i]['pic_id'] = $c->get('pic_id');
			$list[$i]['pic_viewer'] = $c->get('pic_viewer');						
			$list[$i]['do_show'] = $c->get('do_show');		
			$list[$i]['price'] = $c->get('price');
			$list[$i]['price_incl_vat'] = $c->get('price_incl_vat');					
			$i++;
		} 
		
		return $list;

	}
	
	/**
	 * @param $args
	 * - [0] struct credentials
	 * - [1] integer product_id
	 */
	function getProduct($arg) {
		if (is_object($return = $this->checkCredentials($arg[0]))) {
			return $return; 
		}

		$this->factoryWebshop();
		
		if (!is_numeric($arg[1])) {
			return new IXR_Error(-5, 'Produktet er ikke et tal');
		}

		$product = new Product($this->kernel, (int)$arg[1]);
		
		return $product->get();

	}
	
	/**
	 * @param $args
	 * - [0] struct credentials
	 * - [1] integer product_id
	 */
	function getRelatedProducts($arg) {
		if (is_object($return = $this->checkCredentials($arg[0]))) {
			return $return; 
		}

		$this->factoryWebshop();
		
		if (!is_numeric($arg[1])) {
			return new IXR_Error(-5, 'Produktet er ikke et tal');
		}

		$product = new Product($this->kernel, (int)$arg[1]);
		
		return $product->getRelatedProducts();

	}
	
  
	
	/**
	 * @param $args
	 * - [0] credentials
	 * - [1] integer product_id
	 */
	
	function addBasket($arg) {

		if (is_object($return = $this->checkCredentials($arg[0]))) {
			return $return; 
		}

		$this->factoryWebshop();		
		
		if (!is_numeric($arg[1])) {
			return new IXR_Error(-5, 'Enten er produktid eller antallet ikke et tal');
		}

		return $this->webshop->basket->add($arg[1]);

	}

	/**
	 * @param $args
	 * - [0] credentials
	 * - [1] integer product_id
	 */

	
	function removeBasket($args) {
		if (is_object($return = $this->checkCredentials($arg[0]))) {
			return $return; 
		}

		$this->factoryWebshop();		
		
		if (!is_numeric($arg[1])) {
			return new IXR_Error(-5, 'Enten er produktid eller antallet ikke et tal');
		}

		return $this->webshop->basket->change($arg[1], $arg[2]);
	}
	
	/**
	 * @param $args
	 * - [0] credentials
	 * - [1] integer product_id
	 * - [2] integer quantity	 
	 */
	
	
	function changeBasket($arg) {
		if (is_object($return = $this->checkCredentials($arg[0]))) {
			return $return; 
		}

		$this->factoryWebshop();		
		
		if (!is_numeric($arg[1]) AND !is_numeric($arg[2])) {
			return new IXR_Error(-5, 'Enten er produktid eller antallet ikke et tal');
     
		}

		return $this->webshop->basket->change($arg[1], $arg[2]);
		
	}

	/**
	 * Basket->getTotalPrice()
	 * @param struct $credentials
	 */
	
	function getTotalPrice($credentials) {
		if (is_object($this->checkCredentials($credentials))) {
			return $return; 
		}

		$this->factoryWebshop();		
		
		return $this->webshop->basket->getTotalPrice();
	
	}
  
	function getTotalWeight($credentials) {
		if (is_object($this->checkCredentials($credentials))) {
			return $return; 
		}
		
		$this->factoryWebshop();		

		return $this->webshop->basket->getTotalWeight();
	
	}  

	/**
	 * Basket->getItems()
	 * @param struct $credentials
	 */
	
	function getItems($credentials) {
		if (is_object($this->checkCredentials($credentials))) {
			return $return; 
		}

		$this->factoryWebshop();

		return $this->webshop->basket->getItems();

	}

	/**
	 * @param $arg
	 * - [0] credentials
	 * - [1] order
	 *
	 * @todo Der skal ske et eller andet, hvis der er noget, der går galt?
	 */
	
	function placeOrder($arg) {
		if (is_object($this->checkCredentials($arg[0]))) {
			return $return; 
		}

		$this->factoryWebshop();
		
		if (count($this->webshop->basket->getItems()) > 0) {
			if ($order_id = $this->webshop->placeOrder($arg[1])) {
				return $order_id;
			}
			else {
				return new IXR_Error(-1, 'Ordren kunne ikke sendes: ' . strtolower(implode(', ', $this->webshop->error->message)));
			}
		}
	}

	/**
	 * @param $arg
	 * - [0] credentials
	 * - [1] order_id
	 * - [2] transaction_number
	 * - [3] transaction_status
	 * - [4] amount
	 *
	 */
	
	function addOnlinePayment($arg) {
		if (is_object($this->checkCredentials($arg[0]))) {
			return $return; 
		}

		$this->factoryWebshop();		
		
		if ($this->webshop->addOnlinePayment($arg[1], $arg[2], $arg[3], $arg[4])) {
			return 1;
		}
		else {
			return 0;
		}

	}
	
}

$server = new WebshopServer();
?>