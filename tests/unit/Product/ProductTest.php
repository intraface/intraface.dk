<?php
require_once dirname(__FILE__) . '/../config.test.php';

require_once 'Intraface/functions.php';
require_once 'Intraface/modules/product/Product.php';
require_once 'Intraface/modules/product/ProductDetail.php';
require_once 'Intraface/shared/keyword/Keyword.php';

error_reporting(E_ALL);

Intraface_Doctrine_Intranet::singleton(1);

class ProductTest extends PHPUnit_Framework_TestCase
{

    function setUp()
    {
        $this->kernel = new Stub_Kernel();

        $db = MDB2::factory(DB_DSN);
        $db->query('TRUNCATE product');
        $db->query('TRUNCATE product_attribute');
        $db->query('TRUNCATE product_attribute_group');
        $db->query('TRUNCATE product_detail');
        $db->query('TRUNCATE product_detail_translation');
        $db->query('TRUNCATE product_variation');
        $db->query('TRUNCATE product_variation_detail');
        $db->query('TRUNCATE product_variation_x_attribute');
        $db->query('TRUNCATE product_x_attribute_group');
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

    function createNewProductWithVariations()
    {
        $product = $this->createProductObject();
        $product->save(array('name' => 'Test', 'price' => 20, 'unit' => 1, 'has_variation' => true));
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

    function testSavesTheValuesGivenAndRemembersOtherValuesFromLastSave()
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
        $this->assertEquals($price, $values['price']);
        $this->assertEquals(1, $values['unit_key']);
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

    function testIsFilledInReturnsZeroWhenNoProductsHasBeenCreated()
    {
        $product = $this->createProductObject();
        $this->assertEquals(0, $product->isFilledIn());
    }

    function testSetRelatedProductReturnsTrueOnSuccessAndOneProductIsReturned()
    {
        $product = $this->createNewProduct();
        $product_to_relate = $this->createNewProduct();

        $this->assertTrue($product->setRelatedProduct($product_to_relate->getId()));

        $this->assertEquals(1, count($product->getRelatedProducts()));
    }

    function testSetAttributeGroupThrowsExceptionOnWhenNotSavedWithVariations()
    {
        $product = $this->createNewProduct();
        try {
            $product->setAttributeGroup(1);
            $this->assertTrue(false, 'An excpetion is not thrown');
        }
        catch(Exception $e) {
            $this->assertEquals('You can not set attribute group for a product without variations!', $e->getMessage());
        }
    }

    function testSetAttributeGroup()
    {
        $product = $this->createNewProductWithVariations();
        $this->assertTrue($product->setAttributeGroup(1));
    }

    function testRemoveAttributeGroup()
    {
        $product = $this->createNewProductWithVariations();
        $product->setAttributeGroup(1);
        $this->assertTrue($product->removeAttributeGroup(1));
    }

    function testGetAttributeGroups()
    {
        $product = $this->createNewProductWithVariations();

        $group = new Intraface_modules_product_Attribute_Group;
        $group->name = 'Test1';
        $group->save();
        $group->load();
        $product->setAttributeGroup($group->getId());

        $group = new Intraface_modules_product_Attribute_Group;
        $group->name = 'Test2';
        $group->save();
        $group->load();
        $product->setAttributeGroup($group->getId());


        $expected = array(
            0 => array(
                'id' => 1,
                'intranet_id' => 1,
                'name' => 'Test1',
                'description' => '',
                '_old_deleted' => 0,
                'deleted_at' => NULL
            ),
            1 => array(
                'id' => 2,
                'intranet_id' => 1,
                'name' => 'Test2',
                'description' => '',
                '_old_deleted' => 0,
                'deleted_at' => NULL
            )
        );
        $this->assertEquals($expected, $product->getAttributeGroups());
    }

    function testGetVariationThrowsExceptionWhenNoGroupsAdded()
    {
        $product = $this->createNewProductWithVariations();
        try {
            $product->getVariation();
            $this->assertTrue(false, 'No exception thrown');
        }
        catch (Exception $e) {
            $this->assertTrue(true);
        }

    }

    function testGetVariation()
    {
        $product = $this->createNewProductWithVariations();
        $group = new Intraface_modules_product_Attribute_Group;
        $group->name = 'Test1';
        $group->save();
        $group->load();
        $product->setAttributeGroup($group->getId());

        $this->assertTrue(is_object($product->getVariation()));


    }

    function testGetVariations()
    {
        $product = $this->createNewProductWithVariations();
        $group = new Intraface_modules_product_Attribute_Group;
        $group->name = 'color';
        $group->attribute[0]->name = 'red';
        $group->attribute[1]->name = 'blue';
        $group->save();
        $product->setAttributeGroup($group->getId());


        $group = new Intraface_modules_product_Attribute_Group;
        $group->name = 'size';
        $group->attribute[0]->name = 'small';
        $group->attribute[1]->name = 'medium';
        $group->save();
        $product->setAttributeGroup($group->getId());

        $variation = $product->getVariation();
        $variation->product_id = 1;
        $variation->setAttributesFromArray(array('attribute1' => 1, 'attribute2' => 3));
        $variation->save();
        $detail = $variation->getDetail();
        $detail->price_difference = 0;
        $detail->weight_difference = 0;
        $detail->save();

        $variation = $product->getVariation();
        $variation->product_id = 1;
        $variation->setAttributesFromArray(array('attribute1' => 2, 'attribute2' => 4));
        $variation->save();
        $detail = $variation->getDetail();
        $detail->price_difference = 0;
        $detail->weight_difference = 0;
        $detail->save();


        $variations = $product->getVariations();

        $this->assertEquals(2, $variations->count());
        $variation = $variations->getFirst();
        $this->assertEquals(1, $variation->getId());
        $this->assertEquals('red', $variation->attribute1->attribute->getName());
        $this->assertEquals('color', $variation->attribute1->attribute->group->getName());

        $this->assertEquals('small', $variation->attribute2->attribute->getName());
        $this->assertEquals('size', $variation->attribute2->attribute->group->getName());

    }

    function testGetPriceInCurrency()
    {
        require_once dirname(__FILE__) .'/../Stub/Fake/Intraface/modules/currency/Currency.php';
        $currency = new Fake_Intraface_modules_currency_Currency;
        require_once dirname(__FILE__) .'/../Stub/Fake/Intraface/modules/currency/Currency/ExchangeRate.php';
        $currency->product_price_exchange_rate = new Fake_Intraface_modules_currency_Currency_ExchangeRate;
        require_once dirname(__FILE__) .'/../Stub/Fake/Ilib/Variable/Float.php';
        $currency->product_price_exchange_rate->rate = new Fake_Ilib_Variable_Float;
        $currency->product_price_exchange_rate->rate->iso = 745.23;

        $product = new Product($this->kernel);
        $product->save(array('name' => 'test', 'price' => 200, 'unit' => 1));
        $product->load();

        $this->assertEquals(26.84, $product->getDetails()->getPriceInCurrency($currency)->getAsIso());



    }

}
