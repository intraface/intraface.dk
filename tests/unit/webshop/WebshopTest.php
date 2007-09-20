<?php
require_once dirname(__FILE__) . '/../config.test.php';

require_once 'PHPUnit/Framework.php';

require_once 'Intraface/Standard.php';
require_once 'Intraface/tools/Date.php';
require_once 'Intraface/Kernel.php';
require_once 'Intraface/modules/webshop/Webshop.php';
require_once 'Intraface/modules/webshop/Basket.php';
require_once 'Intraface/modules/product/ProductDetail.php';

error_reporting(E_ALL);

class FakeWebshopIntranet
{
    public $address;
    function __construct() {
        $this->address = new FakeWebshopAddress;
    }
    function hasModuleAccess() { return true; }
    function get() { return '1'; }
}

class FakeWebshopWeblogin {
    function get() { return 1; }
}

class FakeWebshopAddress
{
    function get($key = '') { if ($key == 'email') return 'lars@legestue.net'; else return 1; }
}

class FakeWebshopSetting {
    function get() { return 1; }
}

class WebshopTest extends PHPUnit_Framework_TestCase
{
    private $webshop;

    function setUp()
    {
        $kernel = new Kernel;
        $kernel->intranet = new FakeWebshopIntranet;
        $kernel->weblogin = new FakeWebshopWeblogin;
        $kernel->setting = new FakeWebshopSetting;
        $this->webshop = new Webshop($kernel, 'thissession');
    }

    ////////////////////////////////////////////////

    function testConstruction()
    {
        $this->assertTrue(is_object($this->webshop));
        $this->assertTrue(is_object($this->webshop->basket));
    }

    function testPlaceOrderReturnsTrue()
    {
        $data = array('name' => 'Customer', 'email' => 'lars@legestue.net', 'type' => 0, 'description' => 'test', 'internal_note' => '', 'message' => '');
        $order_id = $this->webshop->placeOrder($data);
        $this->assertTrue($order_id > 0);
    }

    function testPlaceOrderResetsBasket()
    {
        $data = array('name' => 'Customer', 'email' => 'lars@legestue.net', 'type' => 0, 'description' => 'test', 'internal_note' => '', 'message' => '');
        $order_id = $this->webshop->placeOrder($data);
        $this->assertTrue($order_id > 0);

        $basket = $this->webshop->getBasket();
        $this->assertTrue(count($basket->getItems()) == 0);

    }

}
?>