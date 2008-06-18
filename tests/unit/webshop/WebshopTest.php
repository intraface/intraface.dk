<?php
require_once dirname(__FILE__) . '/../config.test.php';

require_once 'PHPUnit/Framework.php';

require_once 'Intraface/Standard.php';
require_once 'Intraface/Date.php';
require_once 'Intraface/Kernel.php';
require_once 'Intraface/modules/webshop/Webshop.php';
require_once 'Intraface/modules/webshop/Basket.php';
require_once 'Intraface/modules/product/ProductDetail.php';
require_once 'tests/unit/stubs/PhpMailer.php';

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
    private $kernel;

    function setUp()
    {
        $this->kernel = new Intraface_Kernel;
        $this->kernel->intranet = new FakeWebshopIntranet;
        $this->kernel->weblogin = new FakeWebshopWeblogin;
        $this->kernel->setting = new FakeWebshopSetting;
        $this->webshop = new Webshop($this->kernel, 'thissession');
    }

    ////////////////////////////////////////////////

    function testConstruction()
    {
        $this->assertTrue(is_object($this->webshop));
        $this->assertTrue(is_object($this->webshop->basket));
    }

    function testPlaceOrderReturnsAnOrderNumber()
    {
        $data = array('name' => 'Customer', 'email' => 'lars@legestue.net', 'type' => 'private', 'description' => 'test', 'internal_note' => '', 'message' => '');
        $mailer = new FakePhpMailer;
        $order_id = $this->webshop->placeOrder($data, $mailer);
        $this->assertTrue($order_id > 0);
        $this->assertTrue($mailer->isSend(), 'Mail is not send');
        
    }

    function testPlaceOrderResetsBasketSoThereIsNoProductsInBasket()
    {
        $data = array('name' => 'Customer', 'email' => 'lars@legestue.net', 'type' => 'private', 'description' => 'test', 'internal_note' => '', 'message' => '');
        $mailer = new FakePhpMailer;
        $order_id = $this->webshop->placeOrder($data, $mailer);
        $this->assertTrue($order_id > 0);
        $this->assertTrue($mailer->isSend(), 'Mail is not send');

        $basket = $this->webshop->getBasket();
        $this->assertTrue(count($basket->getItems()) == 0);
    }

    function testPlaceOrderWithAnEanNumberSavesTheEanNumberAndAutomaticallyMakesItACompany()
    {
        $ean = '2222222222222';
        $data = array('name' => 'Customer', 'email' => 'lars@legestue.net', 'description' => 'test', 'internal_note' => '', 'message' => '', 'customer_ean' => $ean);
        $mailer = new FakePhpMailer;
        $order_id = $this->webshop->placeOrder($data, $mailer);
        $this->assertTrue($order_id > 0);
        $this->assertTrue($mailer->isSend(), 'Mail is not send');
        
        $order = new Order($this->kernel, $order_id);
        $this->assertEquals($ean, $order->getContact()->getAddress()->get('ean'));
        $this->assertEquals(1, $order->getContact()->get('type_key'));
    }

    function testPlaceManualOrder()
    {
        $ean = '2222222222222';
        $data = array('name' => 'Customer', 'email' => 'lars@legestue.net', 'description' => 'test', 'internal_note' => '', 'message' => '', 'customer_ean' => $ean);
        $mailer = new FakePhpMailer;
        $order_id = $this->webshop->placeManualOrder($data, array(), $mailer);
        $this->assertTrue($order_id > 0);
        $this->assertTrue($mailer->isSend(), 'Mail is not send');

    }

    function testAddOnlinePaymentReturnsZeroWhenNoAccessToOnlinepayment()
    {
        $ean = '2222222222222';
        $data = array('name' => 'Customer', 'email' => 'lars@legestue.net', 'description' => 'test', 'internal_note' => '', 'message' => '', 'customer_ean' => $ean);
        $mailer = new FakePhpMailer;
        $order_id = $this->webshop->placeOrder($data, $mailer);
        $transaction_number = 1000;
        $transaction_status = 'captured';
        $amount = 1000;
        $this->assertTrue($this->webshop->addOnlinePayment($order_id, $transaction_number, $transaction_status, $amount) == 0);
        $this->assertTrue($mailer->isSend(), 'Mail is not send');
    }
}