<?php
require_once 'PHPUnit/Framework.php';

require_once 'Intraface/Standard.php';
require_once 'Intraface/Kernel.php';
require_once 'Intraface/modules/webshop/Basket.php';

class FakeKernel
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
class FakeIntranet
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

class FakeUser
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

class FakeWebshop
{
    public $kernel;
}


define('DB_DSN', 'mysql://root:@localhost/pear');
define('PATH_INCLUDE_MODULE', 'c:/Users/Lars Olesen/workspace/intraface/Intraface/modules/');
define('PATH_INCLUDE_SHARED', 'c:/Users/Lars Olesen/workspace/intraface/Intraface/shared/');
define('PATH_INCLUDE_CONFIG', 'c:/Users/Lars Olesen/workspace/intraface/Intraface/config/');

class BasketTest extends PHPUnit_Framework_TestCase
{

    function setUp()
    {
        $this->emptyBasketTable();
    }

    function emptyBasketTable()
    {
        $db = MDB2::factory(DB_DSN);
        $db->query('TRUNCATE basket');
    }

    function createBasket()
    {
        $kernel = new Kernel;
        $kernel->intranet = new FakeIntranet;
        $kernel->user = new FakeUser;
        $webshop = new FakeWebshop();
        $webshop->kernel = $kernel;
        $basket = new Basket($webshop, 'somesessionid');
        return $basket;
    }

    function testCreateBasket()
    {
        $basket = $this->createBasket();
        $this->assertTrue(is_object($basket));
    }

    function _testAddToBasket()
    {
        $basket = $this->createBasket();

        $product_id = 1;
        $quantity = 1;

        $this->assertTrue($basket->add($product_id, $quantity));
        $this->assertEquals(count($basket->getItems()), 1);
    }

    function testRemoveFromBasket()
    {
        $basket = $this->createBasket();

        $product_id = 1;
        $quantity = 1;

        $basket->add($product_id, $quantity);
        $this->assertEquals(count($basket->getItems()), 1);

        $this->assertTrue($basket->remove($product_id, $quantity));

        $this->assertEquals(count($basket->getItems()), 0);
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