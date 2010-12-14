<?php
require_once dirname(__FILE__) . '/../config.test.php';
require_once 'Intraface/modules/debtor/DebtorItem.php';
require_once 'Intraface/functions.php';
require_once dirname(__FILE__) .'/stubs/Debtor.php';

class DebtorItemTest extends PHPUnit_Framework_TestCase
{
    protected $db;

    function setup()
    {
        $this->db = MDB2::singleton(DB_DSN);
    }

    function tearDown()
    {
        $this->db->query('TRUNCATE debtor_item');
        $this->db->query('TRUNCATE product');
        $this->db->query('TRUNCATE product_detail');
        $this->db->query('TRUNCATE product_detail_translation');
    }

    function createDebtor()
    {
        $debtor = new FakeDebtor;
        $debtor->kernel = new Stub_Kernel;
        return $debtor;
    }

    function createProduct()
    {
        $kernel = new Stub_Kernel;
        require_once 'Intraface/modules/product/Product.php';
        return new Product($kernel);
    }

    function testConstruct()
    {
        $item = new DebtorItem($this->createDebtor());
        $this->assertTrue(is_object($item));
    }

    function testSaveWithEmptyArray() {
        $item = new DebtorItem($this->createDebtor());
        $this->assertFalse($item->save(array()));
        $this->assertTrue($item->error->isError());
    }

    function testSaveWithValidArray() {
        $item = new DebtorItem($this->createDebtor());
        $product = $this->createProduct();
        $product->save(array('name' => 'test', 'vat' => 1, 'price' => '100'));
        $this->assertEquals(1, $item->save(array('product_id' => 1, 'quantity' => 2, 'description' => 'This is a test')));
    }

    function testLoad() {

        $item = new DebtorItem($this->createDebtor());
        $product = $this->createProduct();
        $product->save(array('name' => 'test', 'vat' => 1, 'price' => '100'));
        $item->save(array('product_id' => 1, 'quantity' => 2, 'description' => 'This is a test'));
        $item = new DebtorItem($this->createDebtor(), 1);

        $values = Array(
            'id' => 1,
            'product_id' => 1,
            'product_detail_id' => 1,
            'product_variation_id' => 0,
            'product_variation_detail_id' => 0,
            'description' => 'This is a test',
            'quantity' => 2.00
        );
        $this->assertEquals($values, $item->get());
    }


}

?>