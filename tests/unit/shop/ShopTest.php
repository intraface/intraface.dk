<?php
require_once dirname(__FILE__) . '/../config.test.php';

class FakeShopIntranet
{
    public $address;
    function __construct() {
        $this->address = new FakeWebshopAddress;
    }
    function hasModuleAccess() { return true; }
    function get() { return '1'; }
    function getId() {
        return 1;
    }
}

class FakeShopShop
{
    function getId()
    {
        return 1;
    }
}

class FakeShopWeblogin {
    function get() { return 1; }
}

class FakeShopAddress
{
    function get($key = '') { if ($key == 'email') return 'lars@legestue.net'; else return 1; }
}

class FakeShopSetting {
    function get() { return 1; }
}

class ShopTest extends PHPUnit_Framework_TestCase
{
    private $webshop;
    private $kernel;

    function setUp()
    {
        $this->kernel = new Intraface_Kernel;
        $this->kernel->intranet = new FakeShopIntranet;
        $this->kernel->weblogin = new FakeShopWeblogin;
        $this->kernel->setting = new FakeShopSetting;
        $this->webshop = new Intraface_modules_shop_Coordinator($this->kernel, new FakeShopShop, 'thissession');
    }

    ////////////////////////////////////////////////

    function testConstruction()
    {
        $this->assertTrue(is_object($this->webshop));
        $this->assertTrue(is_object($this->webshop->getBasket()));
    }

    function testPlaceOrderReturnsAnOrderNumber()
    {
        $data = array('name' => 'Customer', 'email' => 'lars@legestue.net', 'type' => 'private', 'description' => 'test', 'internal_note' => '', 'message' => '');
        $order_id = $this->webshop->placeOrder($data);
        $this->assertTrue($order_id > 0);
    }

    function testPlaceOrderResetsBasketSoThereIsNoProductsInBasket()
    {
        $data = array('name' => 'Customer', 'email' => 'lars@legestue.net', 'type' => 'private', 'description' => 'test', 'internal_note' => '', 'message' => '');
        $order_id = $this->webshop->placeOrder($data);
        $this->assertTrue($order_id > 0);

        $basket = $this->webshop->getBasket();
        $this->assertTrue(count($basket->getItems()) == 0);
    }

    function testPlaceOrderWithAnEanNumberSavesTheEanNumberAndAutomaticallyMakesItACompany()
    {
        $ean = '2222222222222';
        $data = array('name' => 'Customer', 'email' => 'lars@legestue.net', 'description' => 'test', 'internal_note' => '', 'message' => '', 'customer_ean' => $ean);
        $order_id = $this->webshop->placeOrder($data);
        $this->assertTrue($order_id > 0);
        $order = new Order($this->kernel, $order_id);
        $this->assertEquals($ean, $order->getContact()->getAddress()->get('ean'));
        $this->assertEquals(1, $order->getContact()->get('type_key'));
    }

    function testPlaceManualOrder()
    {
        $ean = '2222222222222';
        $data = array('name' => 'Customer', 'email' => 'lars@legestue.net', 'description' => 'test', 'internal_note' => '', 'message' => '', 'customer_ean' => $ean);
        $order_id = $this->webshop->placeManualOrder($data);
        $this->assertTrue($order_id > 0);

    }

    function testAddOnlinePaymentReturnsZeroWhenNoAccessToOnlinepayment()
    {
        $ean = '2222222222222';
        $data = array('name' => 'Customer', 'email' => 'lars@legestue.net', 'description' => 'test', 'internal_note' => '', 'message' => '', 'customer_ean' => $ean);
        $order_id = $this->webshop->placeOrder($data);
        $transaction_number = 1000;
        $transaction_status = 'captured';
        $amount = 1000;
        $this->assertTrue($this->webshop->addOnlinePayment($order_id, $transaction_number, $transaction_status, $amount) == 0);
    }
}