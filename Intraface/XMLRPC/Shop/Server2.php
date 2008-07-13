<?php
/**
 * ShopServer
 *
 * @todo we need to move kernel out of Product.
 * @todo we need to move kernel out of DBQuery.
 * @todo we need to find out what to do with hasIntranetAccess and stock
 * @todo we need to work out with getPictures() and Kernel->useModule
 *
 * @category XMLRPC_Server
 * @package  Intraface_XMLRPC_Shop
 * @author   Lars Olesen <lars@legestue.net>
 * @version  @package-version@
 */
class Intraface_XMLRPC_Shop_Server2 extends Intraface_XMLRPC_Server
{
    private $webshop;
    private $basket;
    private $product;

    /**
     * Gets a list with products
     *
     * @param struct $credentials Credentials to use the server
     * @param integer $shop_id    Id for the shop
     * @param array  $search      Optional search array
     *
     * @return array
     */
    public function getProducts($credentials, $shop_id, $search = array())
    {
        $this->checkCredentials($credentials);

        $offset = 0;
        
        $search = $this->processRequestData($search);

        $mixed = array();
        if (!empty($search)) {
            $mixed = $search;
        }

        $search = '';

        $this->_factoryWebshop($shop_id);

        $products = array();

        $area = '';

        if (!empty($mixed['area'])) {
            $area = $mixed['area'];
        }

        $product = new Product($this->webshop->kernel);

        if (!isset($mixed['use_paging']) || $mixed['use_paging'] == 'true') {
            $product->getDBQuery()->usePaging('paging');
        }


        // sublevel has to be used so other searches are not overwritten
        $product->getDBQuery()->storeResult('use_stored', 'webshop_' . $area . '_' .  md5($this->credentials['session_id']), 'sublevel');
        $debug2 = serialize($mixed);
        if (isset($mixed['offset']) AND array_key_exists('offset', $mixed) AND is_numeric($mixed['offset'])) {
            $product->getDBQuery()->useStored(true);
            $product->getDBQuery()->setPagingOffset((int)$mixed['offset']);
            $debug2 .= 'offset ' . $mixed['offset'];
        } elseif (isset($mixed['use_stored']) AND array_key_exists('use_stored', $mixed) AND $mixed['use_stored'] == 'true') {
            $product->getDBQuery()->useStored(true);
            $debug2 .= 'use_stored true';
        } else {
            if (array_key_exists('search', $mixed) AND !empty($mixed['search'])) {
                $product->getDBQuery()->setFilter('search', $mixed['search']);
                $debug2 .= 'search ' . $mixed['search'];
            }

            if (array_key_exists('keywords', $mixed) AND !empty($mixed['keywords'])) {
                $product->getDBQuery()->setFilter('keywords', $mixed['keywords']);
                $debug2 .= 'keyword ' . $mixed['keywords'];
            }
            
            if (array_key_exists('category', $mixed) AND !empty($mixed['category'])) {
                $product->getDBQuery()->setFilter('shop_id', $shop_id);
                $product->getDBQuery()->setFilter('category', $mixed['category']);
                $debug2 .= 'category ' . $mixed['category'];
            }

            if (isset($mixed['ids']) AND array_key_exists('ids', $mixed) AND is_array($mixed['ids'])) {
                $product->getDBQuery()->setFilter('ids', $mixed['ids']);
                $debug2 .= 'ids ' . implode(', ', $mixed['ids']);
            }

            if (array_key_exists('sorting', $mixed) AND !empty($mixed['sorting'])) {
                $product->getDBQuery()->setFilter('sorting', $mixed['sorting']);
                $debug2 .= 'sorting ' . $mixed['sorting'];
            }

        }
        
        $products = array();
        foreach($product->getList('webshop') AS $p) {
            // Make sure we only include necessary data. Several things more might be left out. Mostly we remove description.
            $products[] = array(
                'id' => $p['id'],
                'number' => $p['number'],
                'name' => $p['name'],
                'price' => $p['price'],
                'unit' => $p['unit'],
                'vat' => $p['vat'],
                'weight' => $p['weight'],
                'detail_id' => $p['detail_id'],
                'vat_percent' => $p['vat_percent'],
                'price_incl_vat' => $p['price_incl_vat'],
                'changed_date' => $p['changed_date'],
                'stock' => $p['stock'],
                'has_variation' => $p['has_variation'],
                'pictures' => $p['pictures'],
                'stock_status' => $p['stock_status']);
        }

        return $this->prepareResponseData(array(
            'parameter' => $mixed,
            'debug2' => $debug2,
            'products' => $products,
            'paging' => $product->getDBQuery()->getPaging(),
            'search' => array(),
        ));
    }


    /**
     * Gets one product
     *
     * @param struct  $credentials Credentials to use the server
     * @param integer $shop_id    Id for the shop
     * @param integer $id          Product id
     *
     * @return array
     */
    public function getProduct($credentials, $shop_id, $id)
    {
        $this->checkCredentials($credentials);

        $this->_factoryWebshop($shop_id);

        if (!is_numeric($id)) {
            require_once 'XML/RPC2/Exception.php';
            throw new XML_RPC2_FaultException('product id must be an integer', -5);
        }
        
        $id = $this->processRequestData(intval($id));
        
        $return = array();
        
        $product = new Product($this->kernel, $id);
        $product->getPictures();
        $return['product'] = $product->get();
        if(!$product->get('has_variation') && $product->get('stock')) {
            $return['stock'] = $product->getStock()->get();
        }
        
        if($product->get('has_variation')) {
            $variations = $product->getVariations();
            
            // We should make a Doctrine Product_X_AttributeGroup class and get all the groups i one sql 
            $groups = $product->getAttributeGroups();
            $group_gateway = new Intraface_modules_product_Attribute_Group_Gateway;
            foreach($groups AS $key => $group) {
                // Make sure we only include necessary data
                $return['attribute_groups'][$key]['id'] = $group['id'];
                $return['attribute_groups'][$key]['name'] = $group['name'];
                $attributes = $group_gateway->findById($group['id'])->getAttributes();
                foreach($attributes AS $attribute) {
                    $return['attribute_groups'][$key]['attributes'][] = array(
                        'id' => $attribute->getId(),
                        'name' => $attribute->getName()
                    );
                } 
                    
            }
            
            foreach($variations AS $variation) {
                $detail = $variation->getDetail();
                $attribute_string = '';
                $attributes_array = $variation->getAttributesAsArray();
                foreach($attributes_array AS $attribute) {
                    if($attribute_string != '') $attribute_string .= '-';
                    $attribute_string .= $attribute['id'];
                }
                
                $return['variations'][] = array(
                    'variation' => array(
                        'id' => $variation->getId(),
                        'detail_id' => $detail->getId(),
                        'number' => $variation->getNumber(),
                        'name' => $variation->getName(),
                        'attributes' => $attributes_array,
                        'identifier' => $attribute_string,
                        'price_incl_vat' => round(($product->get('price') + $detail->getPriceDifference()) * (1 + $product->get('vat_percent')/100), 2),
                        'weight' => $product->get('weight') + $detail->getWeightDifference()
                    ),
                    'stock' => $variation->getStock($product)->get()
                );
            }
        }

        return $this->prepareResponseData($return);
    }

    /**
     * Gets related products
     *
     * @param struct  $credentials Credentials to use the server
     * @param integer $shop_id    Id for the shop
     * @param integer $id          Product id
     *
     * @return array
     */
    public function getRelatedProducts($credentials, $shop_id, $product_id)
    {
        $this->checkCredentials($credentials);

        $this->_factoryWebshop($shop_id);
        
        if (!is_numeric($product_id)) {
            require_once 'XML/RPC2/Exception.php';
            throw new XML_RPC2_FaultException('product id must be an integer', -5);
        }

        $product_id = $this->processRequestData(intval($product_id));

        $product = new Product($this->kernel, $product_id);
        return $this->prepareResponseData($product->getRelatedProducts());
    }

   /**
     * Gets featured products
     *
     * Method is experimental and only used by discimport.dk. If you need to use it
     * as well, please contact lars@intraface.dk.
     *
     * @param struct  $credentials Credentials to use the server
     * @param integer $shop_id    Id for the shop
     *
     * @return array
     */
    public function getFeaturedProducts($credentials, $shop_id)
    {
        $related_products = array();

        $this->checkCredentials($credentials);

        $this->_factoryWebshop($shop_id);

        $db = MDB2::factory(DB_DSN);

        if (PEAR::isError($db)) {
            require_once 'XML/RPC2/Exception.php';
            throw new XML_RPC2_FaultException($db->getMessage() . $db->getUserInfo(), -1);
        }

        $featured = new Intraface_modules_shop_FeaturedProducts($this->kernel->intranet, $this->webshop->getShop(), $db);
        $all = $featured->getAll();

        $related_products = array();

        foreach ($all as $row) {
            $product = new Product($this->kernel);
            $product->getDBQuery()->setFilter('keywords', array($row['keyword_id']));

            $related_products[] = array(
                'title' => $row['headline'],
                'products' => $product->getList()
            );

        }

        return $this->prepareResponseData($related_products);

    }

   /**
     * Gets product keywords which can be used to sort ones webshop
     *
     *
     * @param struct  $credentials Credentials to use the server
     * @param integer $shop_id    Id for the shop
     *
     * @return array with id and keywords
     */
    function getProductKeywords($credentials, $shop_id)
    {
        $this->checkCredentials($credentials);
        $this->_factoryWebshop($shop_id);

        $product = new Product($this->kernel);
        $keywords = $product->getKeywordAppender();
        return $this->prepareResponseData($keywords->getUsedKeywords());

    }
    
    /**
     * Returns the categories for the shop
     * 
     * @param struct  $credentials Credentials to use the server
     * @param integer $shop_id    Id for the shop
     *
     * @return array with categories
     * 
     */
    public function getProductCategories($credentials, $shop_id) 
    {
        $category = new Ilib_Category(MDB2::singleton(DB_DSN), 
            new Intraface_Category_Type('shop', $shop_id));

        return $this->prepareResponseData($category->getAllCategories());
    }

    /**
     * Add product to basket
     *
     * @param struct  $credentials       Credentials to use the server
     * @param integer $shop_id    Id for the shop
     * @param integer $produt_id         Product id to add
     * @param integer $product_variation_id Product variation id to change
     * @param integer $quantity          Optional quantity
     * @param string  $text              Extra text to the itemline
     * @param integer $product_detail_id Product detail id
     *
     * @return boolean
     */
    public function addProductToBasket($credentials, $shop_id, $product_id, $product_variation_id, $quantity = 1, $text = '', $product_detail_id = 0)
    {
        if (is_object($return = $this->checkCredentials($credentials))) {
            return $return;
        }

        $this->_factoryWebshop($shop_id);

        if (!is_numeric($product_id)) {
            require_once 'XML/RPC2/Exception.php';
            throw new XML_RPC2_FaultException('product id must be an integer', -5);
        }
        
        $product_id = $this->processRequestData(intval($product_id));
        $product_variation_id = $this->processRequestData(intval($product_variation_id));
        $quantity = $this->processRequestData(intval($quantity));
        $text = $this->processRequestData($text);
        $product_detail_id = $this->processRequestData(intval($product_detail_id));

        return $this->prepareResponseData(
            $this->webshop->getBasket()->add($product_id, $product_variation_id, $quantity, $text, $product_detail_id)
        );
    }

    /**
     * Change the quantity of one product in basket
     *
     * @param struct  $credentials       Credentials to use the server
     * @param integer $shop_id    Id for the shop
     * @param integer $product_id        Product id to change
     * @param integer $product_variation_id Product_variation_id to change
     * @param integer $quantity          New quantity
     * @param string  $text              Extra text to the itemline
     * @param integer $product_detail_id Product detail id
     *
     * @return mixed
     */
    public function changeProductInBasket($credentials, $shop_id, $product_id, $product_variation_id, $quantity, $text = '', $product_detail_id = 0)
    {
        $this->checkCredentials($credentials);

        $this->_factoryWebshop($shop_id);

        if (!is_numeric($product_id) AND !is_numeric($quantity)) {
            require_once 'XML/RPC2/Exception.php';
            throw new XML_RPC2_FaultException('product id and quantity must be integers', -5);
        }
        
        $product_id = $this->processRequestData(intval($product_id));
        $product_variation_id = $this->processRequestData(intval($product_variation_id));
        $quantity = $this->processRequestData(intval($quantity));
        $text = $this->processRequestData($text);
        $product_detail_id = $this->processRequestData(intval($product_detail_id));

        if (!$this->webshop->getBasket()->change($product_id, $product_variation_id, $quantity, $text, $product_detail_id)) {
            return false;
        }

        return true;
    }

    /**
     * Gets an array with the current basket
     *
     * @param struct $credentials Credentials to use the server
     * @param integer $shop_id    Id for the shop
     * @param struct $customer customer values
     *
     * @return array
     */
    public function getBasket($credentials, $shop_id, $customer = array())
    {
        $this->checkCredentials($credentials);

        $this->_factoryWebshop($shop_id);

        $customer = $this->processRequestData($customer);
        
        // we put the possibility for BasketEvaluation not to be run.
        if (is_string($customer) && $customer == 'no_evaluation') {
            // nothing happens
        } elseif (is_array($customer)) {
            $basketevaluation = new Intraface_modules_shop_BasketEvaluation(MDB2::singleton(DB_DSN), $this->webshop->kernel->intranet, $this->webshop->shop);
            if (!$basketevaluation->run($this->webshop->getBasket(), $customer)) {
                // We should see to return the result in some way.
            }
        }

        return $this->prepareResponseData(array(
            'items' => $this->webshop->getBasket()->getItems(),
            'price_total' => $this->webshop->getBasket()->getTotalPrice(),
            'weight' => $this->webshop->getBasket()->getTotalWeight()
        ));
    }

    /**
     * Places an order in Intraface based on the current basket
     *
     * @param struct $credentials Credentials to use the server
     * @param integer $shop_id    Id for the shop
     * @param struct $values      Values to save
     *
     * @return integer $order_id
     */
    public function placeOrder($credentials, $shop_id, $values)
    {
        $this->checkCredentials($credentials);

        $this->_factoryWebshop($shop_id);
        
        $values = $this->processRequestData($values);

        if (!is_array($this->webshop->getBasket()->getItems()) OR count($this->webshop->getBasket()->getItems()) <= 0) {
            require_once 'XML/RPC2/Exception.php';
            throw new XML_RPC2_FaultException('order could not be sent - cart is empty', -4);
        }

        if (empty($values['description'])) {
            $values['description'] = 'Onlineshop';
        }

        if (!$order_id = $this->webshop->placeOrder($values, Intraface_Mail::factory())) {
            require_once 'XML/RPC2/Exception.php';
            throw new XML_RPC2_FaultException('order could not be placed. It returned the following error: ' . strtolower(implode(', ', $this->webshop->error->getMessage())), -4);
        }

        return $this->prepareResponseData($this->webshop->getOrderIdentifierKey());
    }

    /**
     * Saves buyer details
     *
     * @param struct $credentials Credentials to use the server
     * @param integer $shop_id    Id for the shop
     * @param struct $values      Values to save
     *
     * @return boolean true or false
     */
    public function saveAddress($credentials, $shop_id, $values)
    {
        $this->checkCredentials($credentials);

        $this->_factoryWebshop($shop_id);

        if (!is_array($values)) {
            require_once 'XML/RPC2/Exception.php';
            throw new XML_RPC2_FaultException('details could not be saved - nothing to save', -4);
        }
        
        $values = $this->processRequestData($values);
        
        if (!$this->webshop->getBasket()->saveAddress($values)) {
            require_once 'XML/RPC2/Exception.php';
            throw new XML_RPC2_FaultException('datails could not be saved ' . strtolower(implode(', ', $this->webshop->error->getMessage())), -4);
        }

        return $this->prepareResponseData(true);
    }

    /**
     * Get buyer details
     *
     * @param struct  $credentials Credentials to use the server
     * @param integer $shop_id    Id for the shop
     *
     * @return array
     */
    public function getAddress($credentials, $shop_id)
    {
        $this->checkCredentials($credentials);

        $this->_factoryWebshop($shop_id);
        
        return $this->prepareResponseData($this->webshop->getBasket()->getAddress());
    }
    
    /**
     * Saves customer coupon
     *
     * @param struct $credentials     Credentials to use the server
     * @param integer $shop_id    Id for the shop
     * @param string $customer_coupon Customer coupon to save
     *
     * @return boolean true or false
     */
    public function saveCustomerCoupon($credentials, $shop_id, $customer_coupon)
    {
        $this->checkCredentials($credentials);

        $this->_factoryWebshop($shop_id);

        $customer_coupon = $this->processRequestData($customer_coupon);
        if (!$this->webshop->getBasket()->saveCustomerCoupon($customer_coupon)) {
            require_once 'XML/RPC2/Exception.php';
            throw new XML_RPC2_FaultException('datails could not be saved ' . strtolower(implode(', ', $this->webshop->error->getMessage())), -4);
        }

        return $this->prepareResponseData(true);
    }


    /**
     * Get customer coupon
     *
     * @param struct $credentials Credentials to use the server
     * @param integer $shop_id    Id for the shop
     *
     * @return array
     */
    public function getCustomerCoupon($credentials, $shop_id)
    {
        $this->checkCredentials($credentials);

        $this->_factoryWebshop($shop_id);
        return $this->prepareResponseData($this->webshop->getBasket()->getCustomerCoupon());
    }

    /**
     * Saves customer EAN location number
     *
     * @param struct $credentials     Credentials to use the server
     * @param integer $shop_id    Id for the shop
     * @param string $customer_ean Customer EAN to save
     *
     * @return boolean true or false
     */
    public function saveCustomerEan($credentials, $shop_id, $customer_ean)
    {
        $this->checkCredentials($credentials);

        $this->_factoryWebshop($shop_id);

        $customer_ean = $this->processRequestData($customer_ean);
        if (!$this->webshop->getBasket()->saveCustomerEan($customer_ean)) {
            require_once 'XML/RPC2/Exception.php';
            throw new XML_RPC2_FaultException('ean could not be saved ' . strtolower(implode(', ', $this->webshop->error->getMessage())), -4);
        }

        return $this->prepareResponseData(true);
    }


    /**
     * Get customer EAN location number
     *
     * @param struct $credentials Credentials to use the server
     * @param integer $shop_id    Id for the shop
     *
     * @return array
     */
    public function getCustomerEan($credentials, $shop_id)
    {
        $this->checkCredentials($credentials);

        $this->_factoryWebshop($shop_id);

        return $this->prepareResponseData($this->webshop->getBasket()->getCustomerEan());
    }

    /**
     * Saves customer comment
     *
     * @param struct $credentials     Credentials to use the server
     * @param integer $shop_id    Id for the shop
     * @param string $customer_comment Customer coupon to save
     *
     * @return boolean true or false
     */
    public function saveCustomerComment($credentials, $shop_id, $customer_comment)
    {
        $this->checkCredentials($credentials);

        $this->_factoryWebshop($shop_id);

        $customer_comment = $this->processRequestData($customer_comment);
        if (!$this->webshop->getBasket()->saveCustomerComment($customer_comment)) {
            require_once 'XML/RPC2/Exception.php';
            throw new XML_RPC2_FaultException('datails could not be saved ' . strtolower(implode(', ', $this->webshop->error->getMessage())), -4);
        }

        return $this->prepareResponseData(true);
    }


    /**
     * Get customer comment
     *
     * @param struct $credentials Credentials to use the server
     * @param integer $shop_id    Id for the shop
     *
     * @return array
     */
    public function getCustomerComment($credentials, $shop_id)
    {
        $this->checkCredentials($credentials);

        $this->_factoryWebshop($shop_id);

        return $this->prepareResponseData($this->webshop->getBasket()->getCustomerComment());
    }

    /**
     * Get receipt text
     *
     * @param struct $credentials Credentials to use the server
     * @param integer $shop_id    Id for the shop
     *
     * @return array
     */
    public function getReceiptText($credentials, $shop_id)
    {
        $this->checkCredentials($credentials);

        $this->_factoryWebshop($shop_id);

        return $this->prepareResponseData($this->webshop->getReceiptText());
    }

    /**
     * Checks credentials
     *
     * @param struct $credentials Credentials to use the server
     *
     * @return array
     */
    /*
    protected function checkCredentials($credentials)
    {
        $this->credentials = $credentials;

        if (count($credentials) != 2) { // -4
            require_once 'XML/RPC2/Exception.php';
            throw new XML_RPC2_FaultException('wrong argument count in $credentials - got ' . count($credentials) . ' arguments - need 2', -4);
        }
        if (empty($credentials['private_key'])) { // -5
            require_once 'XML/RPC2/Exception.php';
            throw new XML_RPC2_FaultException('supply a private_key', -5);
        }
        if (empty($credentials['session_id'])) { // -5
            require_once 'XML/RPC2/Exception.php';
            throw new XML_RPC2_FaultException('supply a session_id', -5);
        }

        $auth_adapter = new Intraface_Auth_PrivateKeyLogin(MDB2::singleton(DB_DSN), $credentials['session_id'], $credentials['private_key']);
        $weblogin = $auth_adapter->auth();

        if (!$weblogin) {
            require_once 'XML/RPC2/Exception.php';
            throw new XML_RPC2_FaultException('access to intranet denied', -2);
        }

        $this->kernel = new Intraface_Kernel($credentials['session_id']);
        $this->kernel->weblogin = $weblogin;
        $this->kernel->intranet = new Intraface_Intranet($weblogin->getActiveIntranetId());
        $this->kernel->setting = new Intraface_Setting($this->kernel->intranet->get('id'));

        return true;
    }
    */

    /**
     * Initialize the webshop
     *
     * @return void
     */
    private function _factoryWebshop($shop_id)
    {
        if (!$this->kernel->intranet->hasModuleAccess('shop')) {
            require_once 'XML/RPC2/Exception.php';
            throw new XML_RPC2_FaultException('The intranet does not have access to the module webshop', -2);
        }
        $this->kernel->module('shop');

        Doctrine_Manager::connection(DB_DSN);
        $shop = Doctrine::getTable('Intraface_modules_shop_Shop')->findOneById((int)$shop_id);
        if ($shop === false) {
            throw new XML_RPC2_FaultException('Could not find shop', 1);
        }
        $this->webshop = new Intraface_modules_shop_Coordinator($this->kernel, $shop, $this->credentials['session_id']);
    }
}
