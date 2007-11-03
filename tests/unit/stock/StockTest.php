<?php

require_once dirname(__FILE__) . '/../config.test.php';
require_once 'PHPUnit/Framework.php';

require_once 'Intraface/Kernel.php';
require_once 'Intraface/DBQuery.php';
require_once 'Intraface/modules/stock/Stock.php';

error_reporting(E_ALL);

class FakeStockUser {
    function get() {
        return 1;
    }
    function hasModuleAccess()
    {
        return true;
    }
}

class FakeStockIntranet {
    function get() {
        return 1;
    }
    function hasModuleAccess()
    {
        return true;
    }
}

class FakeStockProduct
{
    public $kernel;
    function __construct($kernel)
    {
        $this->kernel = $kernel;
    }
    function get()
    {
        return 1;
    }
}

class StockTest extends PHPUnit_Framework_TestCase
{
    private $stock;

    function setUp()
    {
        $this->kernel = new Kernel();
        $this->kernel->user = new FakeStockUser;
        $this->kernel->intranet = new FakeStockIntranet;
        /*
        $db = MDB2::factory(DB_DSN);
        $db->query('TRUNCATE product');
        $db->query('TRUNCATE product_detail');
        */
        $product = new FakeStockProduct($this->kernel);

        $this->stock = new Stock($product);
    }

    function testConstruction()
    {
        $this->assertTrue(is_object($this->stock));
    }

}
?>