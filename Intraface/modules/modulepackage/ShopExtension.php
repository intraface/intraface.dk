<?php
/**
 * This class takes care of all communication with the intranet maintenance shop.
 *
 * @todo TODO: This is not really a good name
 * @todo TODO: In theory this should be made with some kind of provider choice, so another provider could be selected.
 * @package Intraface_ModulePackage
 * @author sune
 * @version 0.0.1
 */
class Intraface_ModulePackage_ShopExtension {

    /**
     * @var object shop client
     */
    private $shop;

    /**
     * @var object debtor client
     */
    private $debtor;

    /**
     * @var object error
     */

    /**
     * constructor creates the client objects
     *
     * @return void
     */
    function __construct()
    {
        if (!defined('INTRAFACE_INTRANETMAINTENANCE_INTRANET_PRIVATE_KEY') || INTRAFACE_INTRANETMAINTENANCE_INTRANET_PRIVATE_KEY == '') {
            trigger_error("Unable to use shop in Intraface_ModulePackage_ShopExtension as the private key is not set", E_USER_NOTICE);

            return array();
        }

        if (defined('INTRAFACE_XMLRPC_SERVER_URL') && INTRAFACE_XMLRPC_SERVER_URL != '') {
            $xmlrpc_shop_url = INTRAFACE_XMLRPC_SERVER_URL.'shop/server3.php';
            $xmlrpc_debtor_url = INTRAFACE_XMLRPC_SERVER_URL.'debtor/server.php';
        } else {
            $xmlrpc_shop_url = '';
            $xmlrpc_debtor_url = '';
        }

        if (!defined('INTRAFACE_XMLRPC_DEBUG')) {
            define('INTRAFACE_XMLRPC_DEBUG', false);
        }

        try {
            $this->shop = new IntrafacePublic_Shop_XMLRPC_Client(
                array('private_key' => INTRAFACE_INTRANETMAINTENANCE_INTRANET_PRIVATE_KEY, 'session_id' => session_id()),
                INTRAFACE_XMLRPC_DEBUG,
                $xmlrpc_shop_url);
        } catch(Exception $e) {
            $this->shop = NULL;
            trigger_error('Unable to connect to the intranet maintenance webshop', E_USER_ERROR);
        }

        $this->debtor = new IntrafacePublic_Debtor_XMLRPC_Client(
            array('private_key' => INTRAFACE_INTRANETMAINTENANCE_INTRANET_PRIVATE_KEY, 'session_id' => session_id()),
            INTRAFACE_XMLRPC_DEBUG,
            $xmlrpc_debtor_url);

        $this->error = new Intraface_Error;
    }

    /**
     * Returns the product from the shop
     *
     * @param mixed product ids Can either be an integer product id or an array of product ids
     *
     * @return array product
     */
    public function getProduct($product_id = 0)
    {
        if (!isset($this->shop)) {
            return array();
        }

        if (is_array($product_id)) {
            if (count($product_id) > 0) {
                try {
                    $products = $this->shop->getProducts(array('ids' => $product_id, 'use_paging' => false));
                } catch (Exception $e) {
                    $products = array();
                    trigger_error('unable to get products from intranet webshop: '.$e->getMessage(), E_USER_NOTICE);
                }
                return (array)$products;
            } else {
                return array();
            }
        }  elseif (is_int($product_id)) {
            if (intval($product_id) == 0) {
                return array();
            }
            try {
                $product = $this->shop->getProduct($product_id);
            } catch (Exception $e) {
                $products = array();
                trigger_error('unable to get product from intranet webshop: '.$e->getMessage(), E_USER_NOTICE);
            }
            return $product;
        } else {
            trigger_error("Invalid input for ModulePackage->getProduct, should be either array or integer", E_USER_ERROR);
            exit;
        }
    }

    /**
     * Returns detalials of a product
     * Notice the difference between a product, and product detail where the product detail gives specifik information on earlier products
     *
     *
     * @param integer debtor id
     * @param integer product id of the product where you want the product detalil
     *
     * @return mixed on succes returns array of product detail, otherwise returns false
     */
    public function getProductDetailFromExistingOrder($debtor_id, $product_id)
    {
        $debtor = $this->getExistingDebtor($debtor_id);

        // what kind of check of the invoice/order do we need to make here!

        foreach($debtor['items'] AS $item) {
            if ($item['product_id'] == $product_id) {
                return (array)$item;
            }
        }

        return false;

    }

    /**
     * Returns an Existing order
     *
     * @param integer order_id Id of the order
     *
     * @return array Array with order information
     */
    public function getExistingDebtor($debtor_id)
    {
        $debtor = $this->debtor->getDebtor((int)$debtor_id);

        return $debtor;
    }

    /**
     * Places an order in the external shop system
     *
     * @param array customer array with customer information
     * @param array array with products
     *
     * @return integer order id
     */
    public function placeOrder($customer, $products, $mailer)
    {

        if(!is_object($mailer)) {
            throw new Exception('A valid mailer object is needed');
        }
        
        if (!isset($this->shop)) {
            // should we provide an errormessage?
            return false;
        }

        // We need to first add the product to the basket and then afterwards place an order from the basket because of Intraface's shop interface.

        // first we save the address in the basket to be able to evaluate customer coupon.
        if (!$this->shop->saveAddress($customer)) {
            $this->error->set('unable to save the address information');
            return false;
        }

        // then we add the products to the basket
        settype($products, 'array');
        foreach($products AS $product) {
            if (!isset($product['product_detail_id'])) {
                $product['product_detail_id'] = 0;
            }
            if (!$this->shop->changeBasket($product['product_id'], $product['quantity'], $product['description'], $product['product_detail_id'])) {
                $this->error->set("unable to add the product to the basket");
                trigger_error('unable to add the product to the basket', E_USER_NOTICE);
                return false;
            }
        }

        // We get the basket again to get the total price.
        $basket = $this->shop->getBasket();
        if (empty($basket['items'])) {
            $this->error->set("Error processing the order");
            trigger_error('There was no products in the basket', E_USER_NOTICE);
            return false;
        }

        $customer['description'] = 'Intraface Package Add';

        // Then we place the order from the basket. At the moment we need to give the customer again - that is not too clever!
        $order_id = $this->shop->placeOrder($customer, $mailer);

        if ($order_id == 0) {
            $this->error->set("unable to place the order");
            trigger_error('unable to place the order', E_USER_NOTICE);
            false;
        }

        return array('order_id' => $order_id,
            'total_price' => $basket['price_total']);

    }

    /**
     * Adds a payment to a given order
     *
     * @param integer order_id Id of the order
     * @param array payment Array containing information on the payment
     *
     * @return integer Payment id.
     */
    public function addPaymentToOrder($order_id, $payment)
    {
        settype($payment, 'array');
        $payment['belong_to'] = 'order';
        $payment['belong_to_id'] = $order_id;

        return $this->shop->saveOnlinePayment($payment);

    }
}