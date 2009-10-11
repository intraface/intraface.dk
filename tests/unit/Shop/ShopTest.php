<?php
require_once dirname(__FILE__) . '/../config.test.php';
require_once dirname(__FILE__) .'/../stubs/PhpMailer.php';

Intraface_Doctrine_Intranet::singleton(1);

class FakeShopIntranet
{
    public $address;
    function __construct() {
        $this->address = new FakeShopAddress;
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

    function getConfirmationSubject() {
        return 'confirmation subject';
    }

    function getConfirmationText() {
        return 'confirmation text';
    }

    function getConfirmationGreeting()
    {
        return 'confirmation greeting';
    }

    function showPaymentUrl() {
        return 1;
    }

    function getPaymentUrl() {
        return 'payment_url';
    }
    function sendConfirmation()
    {
        return 1;
    }

    function showLoginUrl()
    {
        return true;
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
    protected $backupGlobals = FALSE;

    function setUp()
    {
        $db = MDB2::factory(DB_DSN);
        $result = $db->query('TRUNCATE currency');
        $result = $db->query('TRUNCATE currency_exchangerate');
        $result = $db->query('TRUNCATE contact');
        $result = $db->query('TRUNCATE address');
        $result = $db->query('TRUNCATE debtor');
        $result = $db->query('TRUNCATE email');
        
        
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

    function testPlaceOrderWithWithCurrency()
    {
        $currency = new Intraface_modules_currency_Currency;
        $type = new Intraface_modules_currency_Currency_Type;
        $currency->setType($type->getByIsoCode('EUR'));
        $currency->save();
        
        $rate = new Intraface_modules_currency_Currency_ExchangeRate_ProductPrice;
        $rate->setRate(new Ilib_Variable_Float('745,21', 'da_dk'));
        $rate->setCurrency($currency);
        $rate->save();
        
        $data = array('name' => 'Customer', 'email' => 'lars@legestue.net', 'description' => 'test', 'internal_note' => '', 'message' => '', 'currency' => 'EUR');
        $mailer = new FakePhpMailer;
        $order_id = $this->webshop->placeOrder($data, $mailer);
        $this->assertTrue($order_id > 0);

        $order = new Order($this->kernel, $order_id);
        $this->assertEquals('Intraface_modules_currency_Currency', get_class($order->getCurrency()));
    }

    function testPlaceManualOrder()
    {
        $ean = '2222222222222';
        $data = array('name' => 'Customer', 'email' => 'lars@legestue.net', 'description' => 'test', 'internal_note' => '', 'message' => '', 'ean' => $ean);
        $mailer = new FakePhpMailer;
        $order_id = $this->webshop->placeManualOrder($data, array(), $mailer);
        $this->assertTrue($order_id > 0);
        $this->assertTrue($mailer->isSend(), 'Mail is not send');

    }

    function testAddOnlinePaymentReturnsZeroWhenNoAccessToOnlinepayment()
    {
        $ean = '2222222222222';
        $data = array('name' => 'Customer', 'email' => 'lars@legestue.net', 'description' => 'test', 'internal_note' => '', 'message' => '', 'ean' => $ean);
        $mailer = new FakePhpMailer;
        $order_id = $this->webshop->placeOrder($data, $mailer);
        $transaction_number = 1000;
        $transaction_status = 'captured';
        $amount = 1000;
        $this->assertTrue($this->webshop->addOnlinePayment($order_id, $transaction_number, $transaction_status, $amount) == 0);
        $this->assertTrue($mailer->isSend(), 'Mail is not send');
    }

    function testSaveShop()
    {
        $shop = new Intraface_modules_shop_Shop();
        $shop->name = 'Øer er noget værre noget';
        $shop->save();
    }
}