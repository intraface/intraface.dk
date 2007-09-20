<?php

require_once dirname(__FILE__) . '/../config.test.php';
require_once 'PHPUnit/Framework.php';

require_once 'Intraface/Kernel.php';
require_once 'Intraface/functions/functions.php';
require_once 'Intraface/modules/product/Product.php';
require_once 'Intraface/modules/product/ProductDetail.php';

error_reporting(E_ALL);

class FakeProductUser {
    function get() {
        return 1;
    }
    function hasModuleAccess()
    {
        return true;
    }
}

class FakeProductIntranet {
    function get() {
        return 1;
    }
    function hasModuleAccess()
    {
        return true;
    }
}

class FakeProductKernel {
    public $intranet;
    function useShared() {}
}

class ProductTest extends PHPUnit_Framework_TestCase
{

    function setUp()
    {
        $this->kernel = new Kernel();
        $this->kernel->user = new FakeProductUser;
        $this->kernel->intranet = new FakeProductIntranet;
        $this->kernel->module('product', 1);

        $db = MDB2::factory(DB_DSN);
        $db->query('TRUNCATE product');
        $db->query('TRUNCATE product_detail');
    }

    function createProductObject()
    {
        return new Product($this->kernel);
    }

    function createNewProduct()
    {
        $product = $this->createProductObject();
        $product->save(array('name' => 'Test', 'price' => 20, 'unit' => 1));
        return $product;
    }

    ////////////////////////////////////////////////////////////////////////////

    function testSaveReturnsTrue()
    {
        $product = new Product($this->kernel);
        if (!$result = $product->save(array('name' => 'Test', 'price' => 20, 'unit' => 1))) {
            $product->error->view();
        }
        $this->assertTrue($result > 0);
    }

    function testMaxNumberReturnsZeroWithFirstProductAndOneWithNextProduct()
    {
        $product = $this->createProductObject();
        $this->assertEquals(0, $product->getMaxNumber());
        $product = $this->createNewProduct();
        $this->assertEquals(1, $product->getMaxNumber());
    }

    function testProductCanGetNumberIfOtherProductDontNeedItAnymore()
    {
        $product = new Product($this->kernel);

        $number = $product->getMaxNumber() + 1;

        $new_number = $number + 1;
        if (!$product->save(array('number' => $number, 'name' => 'Test', 'price' => 20, 'unit' => 1))) {
            $product->error->view();
        }

        if (!$product->save(array('number' => $new_number, 'name' => 'Test', 'price' => 20, 'unit' => 1))) {
            $product->error->view();
        }

        $new_product = new Product($this->kernel);

        $array = array('number' => $number, 'name' => 'Test overtager nummer', 'price' => 20, 'unit' => 1);
        if (!$new_product->save($array)) {
            $new_product->error->view();
        }

        $this->assertEquals($number, $new_product->get('number'));
    }


}
?>