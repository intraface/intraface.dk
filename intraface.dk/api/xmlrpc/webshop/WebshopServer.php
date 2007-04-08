<?php
define('FORCE_GZIP', true);
/**
 * WebshopServer
 *
 * DENNE SKAL IKKE LÆNGERE OPDATERES - BRUG I STEDET server2.php
 *
 * Denne server kan levere information til en webshop.
 *
 * Denne klasse er en server, der gør det muligt at lave en webshop på baggrund
 * af intraface-systemet.
 *
 * Klassen <code>extends</code> en grundlæggende klasse, der har en metode
 * til at tjekke brugerens rettigheder.
 *
 * @see XmlRpcServer::checkCredentials() 
 *
 * @author   Lars Olesen <lars@legestue.net>
 * @version  1.0
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
			'Returns an array with all products. Takes two arguments, <var>struct $credentials</var> and <var>string $search</var>.'
		);
		$this->addCallback(
			'products.getProductsByKeywords',
			'this:getProductsByKeywords',
			array('array', 'struct', 'array'),
			'Returns an <var>array</var> with all products. Takes two arguments, <var>struct $credentials</var> and <var>array $keyword_id</var>.'
		); 
		 
		$this->addCallback(
			'products.getProduct',
			'this:getProduct',
			array('array', 'struct', 'integer'),
			'Returns an <var>array</var> with one single product. Takes two arguments, <var>struct $credentials</var> and <var>int $product_id</var>.'
		);
		$this->addCallback(
			'products.getRelatedProducts',
			'this:getRelatedProducts',
			array('array', 'struct', 'integer'),
			'Returns an <var>array</var> with all products related to a certain product. Takes two arguments, <var>struct $credentials</var> and <var>integer $product_id</var>.'
		); 		
		$this->addCallback(
			'basket.add',
			'this:addBasket',
			array('boolean', 'struct', 'integer'),
			'Adds a product to the basket. Takes two arguments, <var>struct $credentials</var> and <var>integer $product_id</var>.'
		);
		/*
		$this->addCallback(
			'basket.remove',
			'this:removeBasket',
			array('boolean', 'struct', 'integer'),
			'Removes a product to the basket. Takes two arguments, <var>struct $credentials</var> and <var>integer $product_id</var>.'
		);
		*/
		$this->addCallback(
			'basket.change',
			'this:changeBasket',
			array('boolean', 'struct', 'integer', 'integer'),
			'Changes a product in basket. Removes a product to the basket. Takes three arguments, <var>struct $credentials</var>, <var>int $product_id</var> and <var>int $change</var>.'
		);
		$this->addCallback(
			'basket.totalPrice',
			'this:getTotalPrice',
			array('float', 'struct'),
			'Returns total price of the basket. Removes a product to the basket. Takes one argument, <var>struct $credentials</var>.'
		);
		$this->addCallback(
			'basket.totalWeight',
			'this:getTotalWeight',
			array('integer', 'struct'),
			'Returns total weight of items in a basket. Removes a product to the basket. Takes one argument, <var>struct $credentials</var>.'
		);
		$this->addCallback(
			'basket.getItems',
			'this:getItems',
			array('array', 'struct'),
			'Returns items in basket. Removes a product to the basket. Takes one argument, <var>struct $credentials</var>.'
		);
		$this->addCallback(
			'basket.placeOrder',
			'this:placeOrder',
			array('boolean', 'struct', 'array'),
			'Places order to the system. Takes two arguments, <var>struct $credentials</var> and <var>array $customer_info</var>.'
		);

		$this->addCallback(
			'payment.addOnlinePayment',
			'this:addOnlinePayment',
			array('boolean', 'struct', 'integer', 'string', 'string', 'double'),
			'Adds onlinepayment to an order. This is experimental.'
		);		

		$this->serve();

	}

	function factoryWebshop() {
		if (!$this->kernel->intranet->hasModuleAccess('webshop')) {
			return new IXR_Error(-2, 'The intranet does not have access to the module webshop');			
		}
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
			$search = strip_tags($arg[1]); 
		}

		
		$this->factoryWebshop();
		
		$products = array();
		
		$product = new Product($this->webshop->kernel);
		$product->dbquery->setFilter('search', $search);
		$products = $product->getList('webshop');
		
		return $products;

	}
	
	/**
	 * Henter en liste med produkter ud fra Keyword
	 *
	 * Metoden bør smelte sammen med getList() i stedet
	 *
	 * @param $args
	 * - [0] $credentials
	 * - [1] optional search term
	 *
	 * @return array
	 * @access public
	 */
	function getProductsByKeywords($arg) {
		$credentials = $arg[0];
		
		if (is_object($return = $this->checkCredentials($credentials))) {
			return $return; 
		}
		
		$this->factoryWebshop();		

		// her bør laves tjek på keyword_id
		// men det kan vist både være en integer og et array med integers?
		$keyword_id = $arg[1];
		
		$list = array();
		
		$product = new Product($this->webshop->kernel);
		$keywords = $product->getKeywords();
		
		$i = 0;	
		foreach ($keywords->getList($keyword_id) AS $key=>$value) {
			$c = new Product($this->webshop->kernel, $value);
			$list[$i] = $c->get();
			
			if (is_object($c->stock)) {
				$list[$i]['stock_status'] = $c->stock->get();
			}
			if ($c->get('do_show') == 0) {
				continue;
			}
			if ($product->kernel->setting->get('intranet', 'webshop.show_online') == 0) { // only stock
				if (array_key_exists('for_sale', $list[$i]['stock_status']) AND $list[$i]['stock_status']['for_sale'] <= 0) {
					continue;
				}
			}	
			/*
			else {
				continue;
				//$list[$i]['actual_stock'] = 0;     
			}
			*/
			/*
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
			*/					
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
		$credentials = $arg[0];
		if (is_object($return = $this->checkCredentials($credentials))) {
			return $return; 
		}

		$this->factoryWebshop();
		
		$id = intval($arg[1]);
		
		if (!is_numeric($id)) {
			return new IXR_Error(-5, 'Produktet er ikke et tal');
		}

		$product = new Product($this->kernel, $id);
		
		return $product->get();

	}
	
	/**
	 * @param $args
	 * - [0] struct credentials
	 * - [1] integer product_id
	 */
	function getRelatedProducts($arg) {
		$credentials = $arg[0];
		if (is_object($return = $this->checkCredentials($credentials))) {
			return $return; 
		}

		$this->factoryWebshop();
		
		$product_id = intval($arg[1]);
		
		if (!is_numeric($product_id)) {
			return new IXR_Error(-5, 'Produktet er ikke et tal');
		}

		$product = new Product($this->kernel, $product_id);
		return $product->getRelatedProducts();

	}
	
  
	
	/**
	 * @param $args
	 * - [0] credentials
	 * - [1] integer product_id
	 */
	
	function addBasket($arg) {

		$credentials = $arg[0];
	
		if (is_object($return = $this->checkCredentials($credentials))) {
			return $return; 
		}

		$this->factoryWebshop();		
		
		$product_id = intval($arg[1]);
		
		if (!is_numeric($product_id)) {
			return new IXR_Error(-5, 'Produkt_id er ikke et tal');
		}

		return $this->webshop->basket->add($product_id);

	}

	/**
	 * @param $args
	 * - [0] credentials
	 * - [1] integer product_id
	 */

	/*
	function removeBasket($args) {
		$credentials = $arg[0];
	
		if (is_object($return = $this->checkCredentials($credentials))) {
			return $return; 
		}

		$this->factoryWebshop();		
		
		$product_id = $arg[1];
		
		if (!is_numeric($product_id)) {
			return new IXR_Error(-5, 'Produktid er ikke et tal');
		}

		return $this->webshop->basket->change($arg[1], $arg[2]);
	}
	*/
	
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
		
		$product_id = intval($arg[1]);
		$quantity = intval($arg[2]);
		
		if (!is_numeric($product_id) AND !is_numeric($quantity)) {
			return new IXR_Error(-5, 'Enten er produktid eller antallet ikke et tal');
     
		}

		return $this->webshop->basket->change($product_id, $quantity);
		
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
		$credentials = $arg[0];
		if (is_object($this->checkCredentials($credentials))) {
			return $return; 
		}
		$this->factoryWebshop();

		// indhold sendt over xml-rpc er altid utf8 - derfor skal det decodes
		// der er vist så vidt jeg ved ikke nogen problemer i at decode det? Det
		// skal dog kun bruges så længe vi ikke selv kører utf8!
		$values = array_map('utf8_decode', $arg[1]);
		
		if (is_array($this->webshop->basket->getItems()) AND count($this->webshop->basket->getItems()) > 0) {
			if ($order_id = $this->webshop->placeOrder($values)) {
				return $order_id;
			}
			else {
				return new IXR_Error(-6, 'Ordren kunne ikke sendes: ' . strtolower(implode(', ', $this->webshop->error->message)));
			}
		}
		else {
			return new IXR_Error(-4, 'Der er ikke noget i kurven, så ordren kunne ikke sendes.');
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
			return new IXR_Error(-6, 'Onlinebetalingen kunne ikke tilføjes ' . strtolower(implode(', ', $this->webshop->error->message)));		
		}

	}
	
}
if($_SERVER['REQUEST_METHOD'] != 'POST' || $_SERVER['CONTENT_TYPE'] == 'application/x-www-form-urlencoded') {
	require('../Documentor.php');
	$doc = new XMLRPC_Documentor('http://www.intraface.dk' . $_SERVER['PHP_SELF']);

	echo $doc->display();
}
else {
	$server = new WebshopServer();
}
?>