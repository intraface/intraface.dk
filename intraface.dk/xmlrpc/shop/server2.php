<?php
/**
 * WebshopServer2
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

$HTTP_RAW_POST_DATA = file_get_contents('php://input');
require('../../common.php');
require_once('../XmlRpcServer.php');

class WebshopServer2 extends XmlRpcServer {

  var $kernel;
  var $webshop;
  var $basket;
  var $product;
  var $credentials;

  function WebshopServer2() {
    WebshopServer2::__construct();
  }

  function __construct() {
    XmlRpcServer::XmlRpcServer();

    $this->addCallback(
      'products.getList',
      'this:getProducts',
      array('array', 'struct', 'array'),
      'Returns an array with all products. Takes two arguments, <var>struct $credentials</var> and <var>string $search</var>.'
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
      'Changes a product in basket. Takes three arguments, <var>struct $credentials</var>, <var>int $product_id</var> and <var>int $change</var>. Returns error -100 if no more is on stock.'
    );
    $this->addCallback(
      'basket.totalPrice',
      'this:getTotalPrice',
      array('float', 'struct'),
      'Returns total price of the basket. Takes one argument, <var>struct $credentials</var>.'
    );
    $this->addCallback(
      'basket.totalWeight',
      'this:getTotalWeight',
      array('integer', 'struct'),
      'Returns total weight of items in a basket. Takes one argument, <var>struct $credentials</var>.'
    );
    $this->addCallback(
      'basket.getItems',
      'this:getItems',
      array('array', 'struct'),
      'Returns items in basket. Takes one argument, <var>struct $credentials</var>.'
    );

    $this->addCallback(
      'basket.get',
      'this:getBasket',
      array('array', 'struct'),
      'Returns array with items, price and weight in basket. Takes one argument, <var>struct $credentials</var>.'
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
    $credentials = $arg[0];

    if (is_object($return = $this->checkCredentials($credentials))) {
      return $return;
    }

    $search = '';
    $offset = 0;


    $mixed = array();
    if (!empty($arg[1])) {
      $mixed = $arg[1];
    }

    /*
    area bruges til hvis man skal have forskel på de forskellige area-søgninger
    array('area' => 'uniqe_string', 'search' => 'søgestreng', 'keywords' => 'keywords', 'use_stored' => true, 'offset' => 20, 'sorting' => 'date / name');

    */


    $this->factoryWebshop();

    $products = array();

    $area = '';

    if (!empty($mixed['area'])) {
      $area = $mixed['area'];
    }

    $product = new Product($this->webshop->kernel);
    $product->createDBQuery();

    //if (array_key_exists('use_paging', $mixed) AND $mixed['use_paging'] == 'true') {
      $product->dbquery->usePaging('paging');
    //}

    /*
    if (array_key_exists('limit', $mixed) AND is_numeric($mixed['limit'])) {
      $product->dbquery->setLimit($mixed['limit']);
    }
    */

    # sublevel skal bruges for ikke at overskrive de andre søgninger
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

    if (!$this->webshop->basket->change($product_id, $quantity)) {
      return new IXR_Error(-100, 'Så mange er der ikke på lager');
    }
    return 1;

  }


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
   * @param $arg
   * - [0] credentials
   * - [1] order
   *
   * @todo Der skal ske et eller andet, hvis der er noget, der går galt?
   */

  function placeOrder($arg) {
    $credentials = $arg[0];
    if (is_object($return = $this->checkCredentials($credentials))) {
      return $return;
    }
    $this->factoryWebshop();

    // indhold sendt over xml-rpc er altid utf8 - derfor skal det decodes
    // der er vist så vidt jeg ved ikke nogen problemer i at decode det? Det
    // skal dog kun bruges så længe vi ikke selv kører utf8!
    $values = array_map('utf8_decode', $arg[1]);

    if (!is_array($this->webshop->basket->getItems()) OR count($this->webshop->basket->getItems()) <= 0) {
      return new IXR_Error(-4, 'Der er ikke noget i kurven, så ordren kunne ikke sendes.');
    }

    $values['description'] = 'Webshop';

    if (!$order_id = $this->webshop->placeOrder($values)) {
      return new IXR_Error(-6, 'Ordren kunne ikke sendes: ' . strtolower(implode(', ', $this->webshop->error->message)));
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

  function addOnlinePayment($arg) {
    if (is_object($return = $this->checkCredentials($arg[0]))) {
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
  $doc = new XMLRPC_Documentor('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']);

  echo $doc->display();
}
else {
  $server = new WebshopServer2();
}
?>