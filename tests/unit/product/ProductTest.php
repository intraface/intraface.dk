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

    function createProductObject($id = 0)
    {
        return new Product($this->kernel, $id);
    }

    function createNewProduct()
    {
        $product = $this->createProductObject();
        $product->save(array('name' => 'Test', 'price' => 20, 'unit' => 1));
        return $product;
    }

    ////////////////////////////////////////////////////////////////////////////

    function testSavesProductsAndReturnsIdOfProductOnSuccess()
    {
        $product = new Product($this->kernel);
        $name = 'Test';
        $price = 20;
        if (!$result = $product->save(array('name' => $name, 'price' => $price, 'unit' => 1))) {
            $product->error->view();
        }
        $this->assertTrue($result > 0);
        $values = $product->get();

        $this->assertEquals(1, $values['number']);
        $this->assertEquals($name, $values['name']);
        $this->assertEquals($price, $values['price']);
    }

    function testSavePersistsItselfAndCanSaveAgainStillHavingTheSameNumber()
    {
        $product = new Product($this->kernel);
        $name = 'Test';
        $price = 20;
        if (!$result = $product->save(array('name' => $name, 'price' => $price, 'unit' => 1))) {
            $product->error->view();
        }
        $this->assertTrue($result > 0);
        $values = $product->get();

        $this->assertEquals(1, $values['number']);
        $this->assertEquals($name, $values['name']);
        $this->assertEquals($price, $values['price']);

        $product = new Product($this->kernel, $result);
        $name = 'Test';
        $price = 20;
        if (!$result = $product->save(array('number' => $values['number'], 'name' => $name . '2', 'price' => $price, 'unit' => 1))) {
            $product->error->view();
        }
        $this->assertTrue($result > 0);
        $values = $product->get();

        $this->assertEquals(1, $values['number']);
        $this->assertEquals($name . '2', $values['name']);
        $this->assertEquals($price, $values['price']);
    }

    function testSaveStateAccountIdInProductDetailsDoesntChangeOtherValues()
    {
        $product = new Product($this->kernel);
        $name = 'Test';
        $price = 20;
        if (!$id = $product->save(array('name' => $name, 'price' => $price, 'unit' => 1, 'state_account_id' => 10))) {
            $product->error->view();
        }

        $this->assertTrue($id > 0);
        $values = $product->get();

        $this->assertEquals(1, $values['number']);
        $this->assertEquals($name, $values['name']);
        $this->assertEquals($price, $values['price']);
        $this->assertEquals(10, $values['state_account_id']);

        $product = new Product($this->kernel, $id);
        $this->assertEquals($id, $product->getId());
        $this->assertTrue($product->getDetails()->setStateAccountId(20));
        $this->assertEquals($id, $product->getId());

        $values = $product->get();

        $this->assertEquals(1, $values['number']);
        $this->assertEquals($name, $values['name']);
        $this->assertEquals(20, $values['state_account_id']);
    }

    function testSaveOnlySavesTheValuesWhichAreGiven()
    {
        $product = new Product($this->kernel);
        $name = 'Test';
        $price = 20;
        if (!$id = $product->save(array('name' => $name, 'price' => $price, 'unit' => 1, 'state_account_id' => 10))) {
            $product->error->view();
        }

        $this->assertTrue($id > 0);
        $values = $product->get();

        $this->assertEquals(1, $values['number']);
        $this->assertEquals($name, $values['name']);
        $this->assertEquals($price, $values['price']);
        $this->assertEquals(10, $values['state_account_id']);

        $product = new Product($this->kernel, $id);
        $values = $product->get();
        //print_r($values);


        $data = array('state_account_id' => 20);
        $product->save($data);
        $values = $product->get();
        //print_r($values);

        $this->assertEquals(1, $values['number']);
        $this->assertEquals($name, $values['name']);
        $this->assertEquals(20, $values['state_account_id']);

    }

    function testMaxNumberIncrementsOnePrProductAdded()
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

    function testDeleteAProduct()
    {
        $product = $this->createNewProduct();
        $this->assertTrue($product->delete());
        $this->assertFalse($product->isActive());
    }

    function testUnDeleteAProduct()
    {
        $product = $this->createNewProduct();
        $product->delete();
        $this->assertFalse($product->isActive());
        $this->assertTrue($product->undelete());
        $this->assertTrue($product->isActive());
    }

    function testAProductCanStillBeLoadedEvenIfDeleted()
    {
        $product = $this->createNewProduct();
        $product_id = $product->get('id');
        $product->delete();

        $deletedproduct = $this->createProductObject($product_id);

        $this->assertEquals($product->get('id'), $deletedproduct->get('id'));
        $this->assertEquals($product->get('name'), $deletedproduct->get('name'));
        $this->assertEquals($product->get('price'), $deletedproduct->get('price'));
    }

    function testCopyProduct()
    {
        $product = $this->createNewProduct();
        $new_id = $product->copy();

        $newproduct = $this->createProductObject($new_id);

        $this->assertEquals(2, $newproduct->get('number'));
        $this->assertEquals('Test (kopi)', $newproduct->get('name'));
    }
}
?>