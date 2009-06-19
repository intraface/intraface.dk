<?php
require_once dirname(__FILE__) . '/../config.test.php';

class FakeShopBasketKernel
{
    public $intranet;
    public $user;
    function useModule()
    {
        return true;
    }
    function useShared()
    {
        return true;
    }
}
class FakeShopBasketIntranet
{
    function get()
    {
        return 1;
    }
    function hasModuleAccess()
    {
        return true;
    }

    function getId()
    {
        return 1;
    }
}

class FakeShopBasketUser
{
    function hasModuleAccess()
    {
        return true;
    }
    function get()
    {
        return 1;
    }
    function getActiveIntranetId() {
        return 1;
    }

} // used for DBQuery

class FakeShopBasketCoordinator
{
    public $kernel;
}

class FakeShopBasketWebshop
{
    function getId()
    {
        return 1;
    }
}


class ShopBasketTest extends PHPUnit_Framework_TestCase
{
    private $product;
    protected $backupGlobals = FALSE;

    function setUp()
    {
        $this->emptyBasketTable();
        $kernel = $this->createKernel();
        $kernel->module('product');
        $this->kernel = $kernel;
        $this->product = new Product($kernel);
        $this->product->save(array('name' => 'test', 'price' => 200, 'weight' => 200));


    }

    function tearDown() {
        $this->product->delete();
    }

    function createProductWithVariations()
    {
        require_once 'install/Helper/Product.php';
        $helper = new Install_Helper_Product($this->kernel, MDB2::factory(DB_DSN));
        $helper->createWithVariations();
    }

    function emptyBasketTable()
    {
        $db = MDB2::factory(DB_DSN);
        $result = $db->query('TRUNCATE basket');
        $result = $db->query('TRUNCATE basket_details');
        $result = $db->query('TRUNCATE product');
        $result = $db->query('TRUNCATE product_detail');
        $result = $db->query('TRUNCATE product_attribute');
        $result = $db->query('TRUNCATE product_attribute_group');
        $result = $db->query('TRUNCATE product_attribute');
        $result = $db->query('TRUNCATE product_variation');
        $result = $db->query('TRUNCATE product_variation_detail');
        $result = $db->query('TRUNCATE product_variation_x_attribute');
        $result = $db->query('TRUNCATE product_x_attribute_group');

    }

    function createKernel()
    {
        $kernel = new Intraface_Kernel;
        $kernel->intranet = new FakeShopBasketIntranet;
        $kernel->user = new FakeShopBasketUser;
        return $kernel;
    }

    function createBasket()
    {
        $kernel = $this->createKernel();
        $coordinator = new FakeShopBasketCoordinator();
        $shop = new FakeShopBasketWebshop;
        $coordinator->kernel = $kernel;
        $basket = new Intraface_modules_shop_Basket(MDB2::factory(DB_DSN), new FakeShopBasketIntranet, $coordinator, $shop, 'somesessionid');
        return $basket;
    }

    function testCreateBasket()
    {
        $basket = $this->createBasket();
        $this->assertTrue(is_object($basket));
    }

    function testAddToBasket()
    {

        $basket = $this->createBasket();

        $product_id = 1;
        $quantity = 1;

        $this->assertTrue($basket->add($product_id, 0, $quantity));

        $items = $basket->getItems();

        $this->assertEquals(count($items), 1);
        $this->assertEquals($items[0]['quantity'], $quantity);
        $this->assertEquals($items[0]['product_id'], $product_id);

    }

    function testAddToBasketWithVariation()
    {
        $this->createProductWithVariations();
        $basket = $this->createBasket();

        $product_id = 2;
        $product_variation_id = 3;
        $quantity = 1;

        $this->assertTrue($basket->add($product_id, $product_variation_id, $quantity));

        $items = $basket->getItems();

        $this->assertEquals(count($items), 1);
        $this->assertEquals($items[0]['quantity'], $quantity);
        $this->assertEquals($items[0]['product_id'], $product_id);
        $this->assertEquals($items[0]['product_variation_id'], $product_variation_id);
    }

    function testRemoveFromBasket()
    {
        $basket = $this->createBasket();

        $product_id = 1;
        $quantity = 1;

        $basket->add($product_id, 0, $quantity);
        $this->assertEquals(count($basket->getItems()), 1);

        $this->assertTrue($basket->remove($product_id, 0, $quantity));

        $items = $basket->getItems();

        $this->assertEquals(count($items), 0);
    }

    function testChangeBasket()
    {
        $basket = $this->createBasket();

        $product_id = 1;
        $quantity = 1;

        $basket->change($product_id, 0, $quantity);

        $items = $basket->getItems();
        $this->assertEquals($items[0]['quantity'], $quantity);

        $new_quantity = 10;

        $this->assertTrue($basket->change($product_id, 0, $new_quantity));

        $items = $basket->getItems();

        $this->assertEquals($items[0]['quantity'], $new_quantity);
    }

    function testBasketCanContainANegativeQuanityOfProducts()
    {
        $basket = $this->createBasket();

        $product_id = 1;
        $quantity = 1;

        $basket->change($product_id, 0, $quantity);

        $items = $basket->getItems();

        $this->assertEquals($items[0]['quantity'], $quantity);


        $basket->change($product_id, 0, -1);
        $items = $basket->getItems();

        $this->assertEquals($items[0]['quantity'], -1);

    }

    function testResetBasket()
    {
        $basket = $this->createBasket();

        $product_id = 1;
        $quantity = 1;

        $basket->change($product_id, 0, $quantity);

        $items = $basket->getItems();
        $this->assertEquals(count($items), 1);

        $basket->reset();

        $items = $basket->getItems();

        $this->assertEquals(count($items), 0);

    }

    function testRemoveEvaluationProducts()
    {
        $basket = $this->createBasket();

        $product_id = 1;
        $quantity = 1;
        $evaluation_product = 1;

        $this->assertTrue($basket->change($product_id, 0, $quantity, '', '', $evaluation_product));

        $this->assertEquals(count($basket->getItems()), 1);

        $this->assertTrue($basket->removeEvaluationProducts());

         $this->assertEquals(count($basket->getItems()), 0);
    }

    function testSaveCustomerEan() {
        $basket = $this->createBasket();

        $ean = '1234567890123';

        $this->assertTrue($basket->saveCustomerEan($ean));

        $this->assertEquals($basket->getCustomerEan(), array('customer_ean' => $ean));
    }

    function testSaveCustomerCoupon() {
        $basket = $this->createBasket();

        $coupon = '12345';

        $this->assertTrue($basket->saveCustomerCoupon($coupon));

        $this->assertEquals($basket->getCustomerCoupon(), array('customer_coupon' => $coupon));
    }

    function testSaveCustomerComment() {
        $basket = $this->createBasket();

        $comment = 'this is a comment';

        $this->assertTrue($basket->saveCustomerComment($comment));

        $this->assertEquals($basket->getCustomerComment(), array('customer_comment' => $comment));
    }

    function testSaveAddressOnFullAddress() {
        $basket = $this->createBasket();

        $address = array('name' => 'my name',
            'contactperson' => 'my contactperson name',
            'address' => 'my address',
            'postcode' => '1234',
            'city' => 'my city',
            'country' => 'my country',
            'cvr' => '12345678',
            'email' => 'email@intraface.dk',
            'phone' => '87654321');

        $this->assertTrue($basket->saveAddress($address));

        $this->assertEquals($address, $basket->getAddress());
    }

    function testSaveAddressOnIncompleteAddress() {
        $basket = $this->createBasket();

        $address = array('name' => 'my name',
            'address' => 'my address',
            'city' => 'my city');

        $address_return = array('name' => 'my name',
            'contactperson' => '',
            'address' => 'my address',
            'postcode' => '',
            'city' => 'my city',
            'country' => '',
            'cvr' => '',
            'email' => '',
            'phone' => '');

        $this->assertTrue($basket->saveAddress($address));

        $this->assertEquals($address_return, $basket->getAddress());
    }

    function testGetTotalPriceCalculatesCorrect()
    {
        // This test is not as relevant after variation price difference has been deactivated.
        $this->createProductWithVariations();
        $basket = $this->createBasket();

        $basket->add(1, 0, 3); // 200 * 3 + vat
        $basket->add(2, 3, 2); // 100 * 2 + vat

        $this->assertEquals(1000, $basket->getTotalPrice());
    }

    function testGetTotalWeightCalculatesCorrect()
    {
        $this->createProductWithVariations();
        $basket = $this->createBasket();

        $basket->add(1, 0, 3); // 200 * 3
        $basket->add(2, 3, 2); // 104 * 2

        $this->assertEquals(808, $basket->getTotalWeight());
    }

}
?>