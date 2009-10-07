<?php
require_once dirname(__FILE__) . '/../config.test.php';
require_once 'PHPUnit/Framework.php';

require_once 'Intraface/modules/debtor/DebtorItem.php';
require_once 'Intraface/functions.php';
require_once dirname(__FILE__) .'/../stubs/Kernel.php';
require_once dirname(__FILE__) .'/../stubs/Intranet.php';
require_once dirname(__FILE__) .'/stubs/Debtor.php';
// require_once 'Intraface/DBQuery.php';


class DebtorItemTest extends PHPUnit_Framework_TestCase
{
    function setup() {
        $db = MDB2::factory(DB_DSN);
        $db->query('TRUNCATE debtor_item');
        $db->query('TRUNCATE product');
        $db->query('TRUNCATE product_detail');
        $db->query('TRUNCATE product_detail_translation');
        
        
    }
    
    
    function createDebtor()
    {
        $debtor = new FakeDebtor;
        $debtor->kernel = $kernel = new FakeKernel;
        $debtor->kernel->intranet = new FakeIntranet;
        return $debtor;
    }
    
    function createProduct()
    {
        $kernel = new FakeKernel;
        $kernel->intranet = new FakeIntranet;
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