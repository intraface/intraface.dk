<?php
require_once 'PHPUnit/Framework.php';

require_once 'Intraface/Standard.php';
require_once 'Intraface/Kernel.php';
require_once 'Intraface/modules/webshop/Basket.php';

class FakeKernel {
    public $intranet;
    function useModule()
    {
        return true;
    }
    function useShared()
    {
        return true;
    }
}
class FakeIntranet {
    function get() {
        return 1;
    }
    function hasModuleAccess()
    {
        return true;
    }
}
class FakeWebshop {
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

    function testAddToBasket()
    {
        $basket = $this->createBasket();

        $product_id = 1;
        $quantity = 1;

        $this->assertTrue($basket->add($product_id, $quantity));
    }

    function testRemoveFromBasket()
    {
        $basket = $this->createBasket();

        $product_id = 1;
        $quantity = 1;

        $this->assertTrue($basket->remove($product_id, $quantity));
    }

}
?>