<?php
/**
 * This class contains actions that is needed to perform to add and remove the correct packages
 * when adding og upgrading af package.
 * It contains several methods which can be performed on the action, such as placing the order and executing the actions to the correct module packages for the intranet.
 *
 * @package Intraface_modules_modulepackage
 * @author Sune Jensen
 * @version 0.0.1
 */
class Intraface_modules_modulepackage_Action
{

    /*
     * @var array
     */
    private $action = array();

    /**
     *
     * @var integer $intranet_id
     */
    private $intranet_id;

    /**
     *
     * @var string $intranet_private_key
     */
    private $intranet_privte_key;

    /**
     * @var array;
     */
    private $basket = array();

    /**
     * @var integer
     */
    private $order_identifier;

    /**
     * @var double
     */
    private $order_total_price;

    /**
     * @var error;
     */
    public $error;

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        $this->error = new Intraface_Error;
    }

    /**
     * Adds an action
     *
     * @param array action  Array with the action.
     *
     * @return boolean true or false
     */
    public function addAction($array)
    {
        $this->action[] = $array;
        return true;
    }


    /**
     * Places the order in an external economic system. The communication with the economic system is handles by Intraface_modules_modulepackage_ShopExtension
     *
     * @param array customer    array with information on customer.
     *
     * @return mixed order id on succes and false on failure.
     */
    public function placeOrder($customer)
    {
        // Because of the building of Intraface Webshop we need to add the order to the basket first
        // Then afterwards we can place the order from the basket.

        // First we translate the actions into actual products for the order
        $products = array();
        foreach ($this->action AS $action) {

            if (isset($action['action']) && isset($action['month']) && isset($action['product_id'])) {
                if ($action['action'] == 'add') {
                    if (isset($action['start_date']) && $action['start_date'] != '' && isset($action['end_date']) && $action['end_date'] != '') {
                        $description = date('d-m-Y', strtotime($action['start_date'])).' - '.date('d-m-Y', strtotime($action['end_date']));
                    }
                    else {
                        $description = '';
                    }

                    $products[] = array(
                        'product_id' => $action['product_id'],
                        'description' => $description,
                        'quantity' => (int)$action['month']);
                }
                elseif (($action['action'] == 'terminate' || $action['action'] == 'delete')
                        && isset($action['product_id']) && $action['product_id'] != 0
                        && isset($action['product_detail_id']) && $action['product_detail_id'] != 0) {
                    // we only substract the price id we are able to find a product detail.
                    $products[] = array(
                        'product_id' => $action['product_id'],
                        'description' => '',
                        'quantity' => (-1*(int)$action['month']),
                        'product_detail_id' => $action['product_detail_id']);

                }
            }
        }

        require_once('Intraface/modules/modulepackage/ShopExtension.php');
        $shop = new Intraface_modules_modulepackage_ShopExtension;
        if (!$order = $shop->placeOrder($customer, $products)) {
            return false;
        }

        $this->order_identifier = $order['order_identifier'];
        $this->order_total_price = $order['total_price'];

        return $this->order_identifier;
    }

    /**
     * returns the order id after the order has been placed
     *
     * @return integer  order id
     */
    public function getOrderIdentifier()
    {
        if(isset($this->order_identifier)) {
            return $this->order_identifier;
        }
        return '';
    }

    /**
     * returns the total price of the order, based on the values from external economic system
     *
     * @return double price
     */
    public function getTotalPrice()
    {
        return (double)$this->order_total_price;
    }

    /**
     * Executes the actions by adding and deleting module packages according to the actions
     *
     * @param object intranet intranet object
     *
     * @return boolean true or false.
     */
    public function execute($intranet)
    {

        if (!is_object($intranet)) {
            throw new Exception("First parameter for Intraface_modules_modulepackage_Action->execute needs to be an intranet object");
            exit;
        }

        foreach ($this->action AS $action) {
            if ($action['action'] == 'add') {
                $manager = new Intraface_modules_modulepackage_Manager($intranet);
                if (!$manager->save($action['module_package_id'], date('d-m-Y', strtotime($action['start_date'])), $action['end_date'])) {
                    throw new Exception('There was an error adding the module package '.$action['module_package_id'], E_USER_NOTICE);
                    $this->error->set("an error appeared when adding your module package");
                }
                if ($this->getOrderIdentifier() != 0) {
                    if (!$manager->addOrderIdentifier($this->getOrderIdentifier())) {
                        throw new Exception('There was an error adding the order '.$this->getOrderId().' to the intranet module package '.$action['module_package_id'], E_USER_NOTICE);
                    }
                }

            }
            elseif ($action['action'] == 'terminate') {
                $manager = new Intraface_modules_modulepackage_Manager($intranet, (int)$action['intranet_module_package_id']);
                if (!$manager->terminate()) {
                    throw new Exception('There was an error terminating the intranet module package '.$action['intranet_module_package_id'], E_USER_NOTICE);
                    $this->error->set("an error appeared when removing your old modulepackage. we have been noticed.");

                }
            }
            elseif ($action['action'] == 'delete') {
                $manager = new Intraface_modules_modulepackage_Manager($intranet, (int)$action['intranet_module_package_id']);
                if (!$manager->delete()) {
                    throw new Exception('There was an error deleting the intranet module package '.$action['intranet_module_package_id'], E_USER_NOTICE);
                    $this->error->set("an error appeared when removing your old modulepackage. we have been noticed.");
                }
            }
        }

        if ($this->error->isError()) {
            return false;
        }
        else {
            return true;
        }
    }

    /**
     * Returns true if their is any add actions wich contains a product_id.
     * This is used to determine whether there should be placed an order.
     *
     * @return boolean true or false
     */
    public function hasAddActionWithProduct()
    {
        if (!is_array($this->action) || count($this->action) == 0) {
            return false;
        }

        foreach ($this->action AS $action) {
            if ($action['action'] == 'add' && $action['product_id'] != 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Set the intranet private key for the intranet of the action
     *
     * @param $key the key of the intranet
     * @return void
     */
    public function setIntranetPrivateKey($key)
    {
        $this->intranet_private_key = $key;
    }

    /**
     * Returns the private key for the intranet
     *
     * @return string intranet private key
     */
    public function getIntranetPrivateKey()
    {
        return $this->intranet_private_key;
    }
}

?>
