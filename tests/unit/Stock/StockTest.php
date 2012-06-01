<?php
require_once 'Intraface/Kernel.php';
require_once 'Intraface/DBQuery.php';
require_once 'Intraface/modules/stock/Stock.php';
require_once 'DB/Sql.php';
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
        $this->kernel = new Stub_Kernel();
        /*
        $db = MDB2::singleton(DB_DSN);
        $db->query('TRUNCATE product');
        $db->query('TRUNCATE product_detail');
        $db->query('TRUNCATE product_detail_translation');
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
