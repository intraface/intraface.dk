<?php
/**
 * WebshopServer
 *
 * @package  Webshop
 * @author   Lars Olesen <lars@legestue.net>
 * @since    0.1.0
 * @version  @package-version@
 */

require_once 'XML/RPC2/Server.php';

class Intraface_XMLRPC_Webshop_Server {

	var $kernel;
	var $webshop;
	var $basket;
	var $product;
	var $credentials;

	function factoryWebshop() {
		if (!$this->kernel->intranet->hasModuleAccess('webshop')) {
			throw new XML_RPC2_FaultException('The intranet does not have access to the module webshop', -2);
		}
		$this->kernel->module('webshop');
		$this->webshop = new Webshop($this->kernel, $this->credentials['session_id']);
	}

	/**
	 * Gets a list with products
	 *
	 * @param struct $credentials
	 * @param array $search optional
	 * @return array
	 */

	public function getProducts($credentials, $search) {
		if (is_object($return = $this->checkCredentials($credentials))) {
			return $return;
		}

		$search = '';
		$offset = 0;


		$mixed = array();
		if (!empty($arg[1])) {
			$mixed = $arg[1];
		}

		$this->factoryWebshop();

		$products = array();

		$area = '';

		if (!empty($mixed['area'])) {
			$area = $mixed['area'];
		}

		$product = new Product($this->webshop->kernel);

		$product->dbquery->usePaging('paging');

		// sublevel has to be used so other searches are not overwritten
		$product->dbquery->storeResult('use_stored', 'webshop_' . $area . '_' .  md5($this->credentials['session_id']), 'sublevel');
		$debug2 = '';
		if (array_key_exists('offset', $mixed) AND is_numeric($mixed['offset'])) {
			$product->dbquery->useStored(true);
			$product->dbquery->setPagingOffset((int)$mixed['offset']);
			$debug2 .= 'offset ' . $mixed['offset'];
		}
		elseif (array_key_exists('use_stored', $mixed) AND $mixed['use_stored'] == 'true') {
			$product->dbquery->useStored(true);
			$debug2 .= 'use_stored true';
		}
		else {
			if (array_key_exists('search', $mixed) AND !empty($mixed['search'])) {
				$product->dbquery->setFilter('search', $mixed['search']);
				$debug2 .= 'search ' . $mixed['search'];
			}

			if (array_key_exists('keywords', $mixed) AND !empty($mixed['keywords'])) {
				$product->dbquery->setFilter('keywords', $mixed['keywords']);
				$debug2 .= 'keyword ' . implode($mixed['keywords'], ',');
			}

			if (array_key_exists('sorting', $mixed) AND !empty($mixed['sorting'])) {
				$product->dbquery->setFilter('sorting', $mixed['sorting']);
				$debug2 .= 'sorting ' . $mixed['sorting'];
			}

		}

		return array(
			'parameter' => $mixed,
			'debug2' => $debug2,
			'products' => $product->getList('webshop'),
			'paging' => $product->dbquery->getPaging(),
			'search' => array(),
		);
	}

	/**
	 * @param struct $credentials
	 * @param integer $id
	 * @return array
	 */
	function getProduct($credentials, $id) {
		if (is_object($return = $this->checkCredentials($credentials))) {
			return $return;
		}

		$this->factoryWebshop();

		$id = intval($id);

		if (!is_numeric($id)) {
			throw new XML_RPC2_FaultException('Produktet er ikke et tal', -5);
		}

		$product = new Product($this->kernel, $id);

		return $product->get();
	}

	/**
	 * @param struct $credentials
	 * @param integer $id
	 * @return array
	 */
	function getRelatedProducts($credentials, $id) {
		if (is_object($return = $this->checkCredentials($credentials))) {
			return $return;
		}

		$this->factoryWebshop();

		$product_id = intval($id);

		if (!is_numeric($product_id)) {
			throw new XML_RPC2_FaultException('Produktet er ikke et tal', -5);
		}

		$product = new Product($this->kernel, $product_id);
		return $product->getRelatedProducts();
	}

	/**
	 * @param struct $credentials
	 * @param integer $id
	 */
	function addBasket($credentials, $id) {
		if (is_object($return = $this->checkCredentials($credentials))) {
			return $return;
		}

		$this->factoryWebshop();

		$product_id = intval($id);

		if (!is_numeric($product_id)) {
			throw new XML_RPC2_FaultException('Produkt_id er ikke et tal', -5);
		}

		return $this->webshop->basket->add($product_id);
	}

	/**
	 * @param struct $credentials
	 * @param integer $product_id
	 * @param integer $quantity
	 * @return mixed
	 */
	function changeBasket($credentials, $product_id, $quantity) {
		if (is_object($return = $this->checkCredentials($arg[0]))) {
			return $return;
		}

		$this->factoryWebshop();

		$product_id = intval($product_id);
		$quantity = intval($quantity);

		if (!is_numeric($product_id) AND !is_numeric($quantity)) {
			throw new XML_RPC2_FaultException('Enten er produktid eller antallet ikke et tal', -5);
		}

		if (!$this->webshop->basket->change($product_id, $quantity)) {
			throw new XML_RPC2_FaultException('Så mange er der ikke på lager', -100);
		}

		return true;
	}

	/**
	 * @param struct $credentials
	 * @return array
	 */
	function getBasket($credentials) {
		if (is_object($return = $this->checkCredentials($credentials))) {
			return $return;
		}

		$this->factoryWebshop();

		return array(
			'items' => $this->webshop->basket->getItems(),
			'price_total' => $this->webshop->basket->getTotalPrice(),
			'weight' => $this->webshop->basket->getTotalWeight()
		);
	}

	/**
	 * @todo Der skal ske et eller andet, hvis der er noget, der går galt?
	 *
	 * @param struct $credentials
	 * @param struct $values
	 * @return integer $order_id
	 */

	function placeOrder($credentials, $values) {
		if (is_object($return = $this->checkCredentials($credentials))) {
			return $return;
		}
		$this->factoryWebshop();

		if (!is_array($this->webshop->basket->getItems()) OR count($this->webshop->basket->getItems()) <= 0) {
			throw new XML_RPC2_FaultException('Der er ikke noget i kurven, så ordren kunne ikke sendes.', -4);
		}

		$values['description'] = 'Webshop';

		if (!$order_id = $this->webshop->placeOrder($values)) {
			throw new XML_RPC2_FaultException('Ordren kunne ikke sendes: ' . strtolower(implode(', ', $this->webshop->error->message)), -4);
		}

		return $order_id;
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
	/*
	function addOnlinePayment($credentials, $order_id, $transaction_number, $transaction_status, $amoung) {
		if (is_object($return = $this->checkCredentials($credentials))) {
			return $return;
		}

		$this->factoryWebshop();

		if ($this->webshop->addOnlinePayment($order_id, $transaction_number, $transaction_status, $amount)) {
			return 1;
		}
		else {
			throw new XML_RPC2_FaultException('Onlinebetalingen kunne ikke tilføjes ' . strtolower(implode(', ', $this->webshop->error->message)), -6);
		}
	}
	*/
}

$server = XML_RPC2_Server::create(new Intraface_XMLRPC_Webshop_Server(), array('prefix' => 'contact.'));
$server->handleCall();
?>