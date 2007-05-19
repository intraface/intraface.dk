<?php
require_once 'PHPUnit/Framework.php';

require_once 'Intraface/Standard.php';
require_once 'Intraface/Kernel.php';
require_once 'Intraface/modules/webshop/Basket.php';
require_once 'Intraface/modules/product/ProductDetail.php';

class FakeBasketKernel
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
class FakeBasketIntranet
{
    function get()
    {
        return 1;
    }
    function hasModuleAccess()
    {
        return true;
    }
}

class FakeBasketUser
{
    function hasModuleAccess()
    {
        return true;
    }
    function get()
    {
        return 1;
    }


} // used for DBQuery

class FakeBasketWebshop
{
    public $kernel;
}


define('DB_DSN', 'mysql://root:@localhost/pear');
define('PATH_INCLUDE_MODULE', 'c:/Users/Lars Olesen/workspace/intraface/Intraface/modules/');
define('PATH_INCLUDE_SHARED', 'c:/Users/Lars Olesen/workspace/intraface/Intraface/shared/');
define('PATH_INCLUDE_CONFIG', 'c:/Users/Lars Olesen/workspace/intraface/Intraface/config/');

class BasketTest extends PHPUnit_Framework_TestCase
{

    private $product;

    function setUp()
    {
        $this->emptyBasketTable();
        $kernel = $this->createKernel();
        $kernel->module('product');
        $this->product = new Product($kernel);
        $this->product->save(array('name' => 'test', 'price' => 200));
    }

    function tearDown() {
        $this->emptyBasketTable();
        $this->product->delete();
    }

    function emptyBasketTable()
    {
        $db = MDB2::factory(DB_DSN);
        $result = $db->query('TRUNCATE basket');
        $result = $db->query('TRUNCATE product');
    }

    function createKernel()
    {
        $kernel = new Kernel;
        $kernel->intranet = new FakeBasketIntranet;
        $kernel->user = new FakeBasketUser;
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

    function testRemoveEvaluationProducts()
    {
        $basket = $this->createBasket();

        $product_id = 1;
        $quantity = 1;
        $evaluation_product = 1;

        $basket->change($product_id, $quantity, '', $evaluation_product);

        $this->assertEquals(count($basket->getItems()), 1);

        $this->assertTrue($basket->removeEvaluationProducts());

         $this->assertEquals(count($basket->getItems()), 0);
    }

}
?>