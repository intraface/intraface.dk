<?php
require_once 'Intraface/modules/webshop/Basket.php';
require_once 'Intraface/modules/product/ProductDetail.php';

class FakeBasketWebshop
{
    public $kernel;
}

class BasketTest extends PHPUnit_Framework_TestCase
{
    private $product;

    function setUp()
    {
        $this->emptyBasketTable();
        $kernel = $this->createKernel();
        $this->product = new Product($kernel);
        $this->product->save(array('name' => 'test', 'price' => 200));
    }

    function tearDown()
    {
        $this->emptyBasketTable();
        $this->product->delete();
    }

    function emptyBasketTable()
    {
        $db = MDB2::singleton(DB_DSN);
        $result = $db->query('TRUNCATE basket');
        $result = $db->query('TRUNCATE basket_details');
        $result = $db->query('TRUNCATE product');
        $result = $db->query('TRUNCATE product_detail');
        $result = $db->query('TRUNCATE product_detail_translation');
    }

    function createKernel()
    {
        $kernel = new Stub_Kernel;
        return $kernel;
    }

    function createBasket()
    {
        $kernel = $this->createKernel();
        $webshop = new FakeBasketWebshop();
        $webshop->kernel = $kernel;
        $basket = new Basket($webshop, 'somesessionid');
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

        $this->assertTrue($basket->add($product_id, $quantity));

        $items = $basket->getItems();

        $this->assertEquals(count($items), 1);
        $this->assertEquals($items[0]['quantity'], $quantity);
        $this->assertEquals($items[0]['product_id'], $product_id);
    }

    function testRemoveFromBasket()
    {
        $basket = $this->createBasket();

        $product_id = 1;
        $quantity = 1;

        $basket->add($product_id, $quantity);
        $this->assertEquals(count($basket->getItems()), 1);

        $this->assertTrue($basket->remove($product_id, $quantity));

        $items = $basket->getItems();

        $this->assertEquals(count($items), 0);
    }

    function testChangeBasket()
    {
        $basket = $this->createBasket();

        $product_id = 1;
        $quantity = 1;

        $basket->change($product_id, $quantity);

        $items = $basket->getItems();
        $this->assertEquals($items[0]['quantity'], $quantity);

        $new_quantity = 10;

        $this->assertTrue($basket->change($product_id, $new_quantity));

        $items = $basket->getItems();

        $this->assertEquals($items[0]['quantity'], $new_quantity);
    }

    function testBasketCanContainANegativeQuanityOfProducts()
    {
        $basket = $this->createBasket();

        $product_id = 1;
        $quantity = 1;

        $basket->change($product_id, $quantity);

        $items = $basket->getItems();

        $this->assertEquals($items[0]['quantity'], $quantity);


        $basket->change($product_id, -1);
        $items = $basket->getItems();

        $this->assertEquals($items[0]['quantity'], -1);
    }

    function testResetBasket()
    {
        $basket = $this->createBasket();

        $product_id = 1;
        $quantity = 1;

        $basket->change($product_id, $quantity);

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

        $this->assertTrue($basket->change($product_id, $quantity, '', '', $evaluation_product));

        $this->assertEquals(count($basket->getItems()), 1);

        $this->assertTrue($basket->removeEvaluationProducts());

         $this->assertEquals(count($basket->getItems()), 0);
    }

    function testSaveCustomerEan()
    {
        $basket = $this->createBasket();

        $ean = '1234567890123';

        $this->assertTrue($basket->saveCustomerEan($ean));

        $this->assertEquals($basket->getCustomerEan(), array('customer_ean' => $ean));
    }

    function testSaveCustomerCoupon()
    {
        $basket = $this->createBasket();

        $coupon = '12345';

        $this->assertTrue($basket->saveCustomerCoupon($coupon));

        $this->assertEquals($basket->getCustomerCoupon(), array('customer_coupon' => $coupon));
    }

    function testSaveCustomerComment()
    {
        $basket = $this->createBasket();

        $comment = 'this is a comment';

        $this->assertTrue($basket->saveCustomerComment($comment));

        $this->assertEquals($basket->getCustomerComment(), array('customer_comment' => $comment));
    }

    function testSaveAddressOnFullAddress()
    {
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

    function testSaveAddressOnIncompleteAddress()
    {
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
}
