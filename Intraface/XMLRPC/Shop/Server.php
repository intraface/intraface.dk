<?php
/**
 * WebshopServer
 *
 * @category XMLRPC_Server
 * @package  Intraface_XMLRPC_Shop
 * @author   Lars Olesen <lars@legestue.net>
 * @version  @package-version@
 */

require_once 'Intraface/Kernel.php';
require_once 'Intraface/Setting.php';
require_once 'Intraface/Intranet.php';
require_once 'Intraface/Weblogin.php';
require_once 'XML/RPC2/Server.php';
require_once 'Intraface/modules/webshop/Webshop.php';

class Intraface_XMLRPC_Shop_Server
{

    var $kernel;
    var $webshop;
    var $basket;
    var $product;
    var $credentials;

    /**
     * Gets a list with products
     *
     * @param struct $credentials Credentials to use the server
     * @param array  $search      Optional search array
     *
     * @return array
     */
    public function getProducts($credentials, $search = array())
    {
        $this->_checkCredentials($credentials);

        $search = '';
        $offset = 0;

        $mixed = array();
        if (!empty($search)) {
            $mixed = $search;
        }

        $this->_factoryWebshop();

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
        } elseif (array_key_exists('use_stored', $mixed) AND $mixed['use_stored'] == 'true') {
            $product->dbquery->useStored(true);
            $debug2 .= 'use_stored true';
        } else {
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
     * Gets one product
     *
     * @param struct  $credentials Credentials to use the server
     * @param integer $id          Product id
     *
     * @return array
     */
    public function getProduct($credentials, $id)
    {
        $this->_checkCredentials($credentials);

        $this->_factoryWebshop();

        $id = intval($id);

        if (!is_numeric($id)) {
            throw new XML_RPC2_FaultException('product id must be an integer', -5);
        }

        $product = new Product($this->kernel, $id);

        return $product->get();
    }

    /**
     * Gets related products
     *
     * @param struct  $credentials Credentials to use the server
     * @param integer $id          Product id
     *
     * @return array
     */
    public function getRelatedProducts($credentials, $id)
    {
        $this->_checkCredentials($credentials);

        $this->_factoryWebshop();

        $product_id = intval($id);

        if (!is_numeric($product_id)) {
            throw new XML_RPC2_FaultException('product id must be an integer', -5);
        }

        $product = new Product($this->kernel, $product_id);
        return $product->getRelatedProducts();
    }

    /**
     * Add product to basket
     *
     * @param struct  $credentials Credentials to use the server
     * @param integer $id          Product id to add
     *
     * @return mixed
     */
    public function addProductToBasket($credentials, $id)
    {
        if (is_object($return = $this->_checkCredentials($credentials))) {
            return $return;
        }

        $this->_factoryWebshop();

        $product_id = intval($id);

        if (!is_numeric($product_id)) {
            throw new XML_RPC2_FaultException('product id must be an integer', -5);
        }

        return $this->webshop->basket->add($product_id);
    }

    /**
     * Change the quantity of one product in basket
     *
     * @param struct  $credentials Credentials to use the server
     * @param integer $product_id  Product id to change
     * @param integer $quantity    New quantity
     *
     * @return mixed
     */
    public function changeProductInBasket($credentials, $product_id, $quantity)
    {
        $this->_checkCredentials($credentials);

        $this->_factoryWebshop();

        $product_id = intval($product_id);
        $quantity = intval($quantity);

        if (!is_numeric($product_id) AND !is_numeric($quantity)) {
            throw new XML_RPC2_FaultException('product id and quantity must be integers', -5);
        }

        if (!$this->webshop->basket->change($product_id, $quantity)) {
            throw new XML_RPC2_FaultException('product quantity is not in stock', -100);
        }

        return true;
    }

    /**
     * Gets an array with the current basket
     *
     * @param struct $credentials Credentials to use the server
     *
     * @return array
     */
    public function getBasket($credentials)
    {
        $this->_checkCredentials($credentials);

        $this->_factoryWebshop();

        return array(
            'items' => $this->webshop->basket->getItems(),
            'price_total' => $this->webshop->basket->getTotalPrice(),
            'weight' => $this->webshop->basket->getTotalWeight()
        );
    }

    /**
     * Places an order in Intraface based on the current basket
     *
     * @param struct $credentials Credentials to use the server
     * @param struct $values      Values to save
     *
     * @return integer $order_id
     */

    public function placeOrder($credentials, $values)
    {
        $this->_checkCredentials($credentials);

        $this->_factoryWebshop();

        if (!is_array($this->webshop->basket->getItems()) OR count($this->webshop->basket->getItems()) <= 0) {
            throw new XML_RPC2_FaultException('order could not be sent - cart is empty', -4);
        }

        $values['description'] = 'Onlineshop';

        if (!$order_id = $this->webshop->placeOrder($values)) {
            throw new XML_RPC2_FaultException('order could not be sent ' . strtolower(implode(', ', $this->webshop->error->message)), -4);
        }

        return $order_id;
    }

    /**
     * Checks credentials
     *
     * @param struct $credentials Credentials to use the server
     *
     * @return array
     */
    private function _checkCredentials($credentials)
    {
        if (count($credentials) != 2) { // -4
            throw new XML_RPC2_FaultException('wrong argument count in $credentials - got ' . count($credentials) . ' arguments - need 2', -4);
        }
        if (empty($credentials['private_key'])) { // -5
            throw new XML_RPC2_FaultException('supply a private_key', -5);
        }
        if (empty($credentials['session_id'])) { // -5
            throw new XML_RPC2_FaultException('supply a session_id', -5);
        }

        $weblogin = new Weblogin();
        if (!$intranet_id = $weblogin->auth('private', $credentials['private_key'], $credentials['session_id'])) {
            throw new XML_RPC2_FaultException('access to intranet denied', -2);
        }

        $this->kernel = new Kernel();
        $this->kernel->weblogin = $weblogin;
        $this->kernel->intranet = new Intranet($intranet_id);
        $this->kernel->setting = new Setting($this->kernel->intranet->get('id'));

        if (!is_object($this->kernel->intranet)) { // -2
            throw new XML_RPC2_FaultException('could not create intranet', -2);
        }

        return true;
    }

    /**
     * Initialize the webshop
     *
     * @return void
     */
    private function _factoryWebshop()
    {
        if (!$this->kernel->intranet->hasModuleAccess('webshop')) {
            throw new XML_RPC2_FaultException('The intranet does not have access to the module webshop', -2);
        }
        $this->kernel->module('webshop');
        $this->webshop = new Webshop($this->kernel, $this->credentials['session_id']);
    }

}
?>