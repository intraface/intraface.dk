<?php
require_once dirname(__FILE__) . '/../config.test.php';

Intraface_Doctrine_Intranet::singleton(1);

class ProductDoctrineTest extends PHPUnit_Framework_TestCase
{

    function setUp()
    {
        $connection = Doctrine_Manager::connection();
        // $query = $connection->getQuery();
        $connection->exec('TRUNCATE product');
        $connection->exec('TRUNCATE product_attribute');
        $connection->exec('TRUNCATE product_attribute_group');
        $connection->exec('TRUNCATE product_detail');
        $connection->exec('TRUNCATE product_detail_translation');
        $connection->exec('TRUNCATE product_variation');
        $connection->exec('TRUNCATE product_variation_detail');
        $connection->exec('TRUNCATE product_variation_x_attribute');
        $connection->exec('TRUNCATE product_x_attribute_group');

        $connection->clear();
    }

    function createProductObject($id = 0)
    {
        if($id != 0) {
            $connection = Doctrine_Manager::connection();
            $connection->clear(); // clear repo, so that we are sure data are loaded again.
            $gateway = new Intraface_modules_product_ProductDoctrineGateway($connection, NULL);
            return $gateway->findById($id);
        }
        return new Intraface_modules_product_ProductDoctrine;
    }

    function createNewProduct()
    {
        $product = $this->createProductObject();
        $product->getDetails()->Translation['da']->name = 'Test';
        $product->getDetails()->Translation['da']->description = '';
        $product->getDetails()->price = new Ilib_Variable_Float(20);
        $product->getDetails()->unit = 1;
        $product->save();
        $product->refresh(true);
        return $product;
        // $product->save(array('name' => 'Test', 'price' => 20, 'unit' => 1));
        // return $product;
    }

    function createNewProductWithVariations()
    {
        $product = $this->createNewProduct();
        $product->has_variation = 1;
        $product->save();
        $product->refresh(true);
        return $product;
        // $product->save(array('name' => 'Test', 'price' => 20, 'unit' => 1, 'has_variation' => true));
        // return $product;
    }

    ////////////////////////////////////////////////////////////////////////////

    function testSaveProductReturnsIdOfProductOnSuccess()
    {
        $product = $this->createProductObject();
        $name = 'Test';
        $price = 20;
        $product->getDetails()->Translation['da']->name = 'Test';
        $product->getDetails()->Translation['da']->description = '';
        $product->getDetails()->price = new Ilib_Variable_Float(20);

        try {
            $product->save();
        } catch (Exception $e) {
            $this->fail($e);
        }

        $product->refresh(true);

        $this->assertEquals(1, $product->getDetails()->getNumber());
        $this->assertEquals($name, $product->getDetails()->getTranslation('da')->name);
        $this->assertEquals($price, $product->getDetails()->getPrice()->getAsIso());
    }

    function testSaveThrowsExceptionOnEmptyProduct()
    {
        $product = $this->createProductObject();
        
        $this->setExpectedException('Exception');
        $product->save();
    }
    
    function testSaveThrowsExceptionOnEmptyProductDetails()
    {
        $product = $this->createProductObject();
        $product->getDetails();
        
        $this->setExpectedException('Exception');
        $product->save();
    }

    function testSaveThrowsExceptionOnEmptyName()
    {
        $product = $this->createProductObject();
        $product->getDetails()->Translation['da']->name = '';
        $this->setExpectedException('Doctrine_Validator_Exception');
        $product->save();
    }

    function testSaveThrowsExceptionOnNoNameButFilledInPrice()
    {
        $product = $this->createProductObject();
        $product->getDetails()->price = new Ilib_Variable_Float(20);
        $this->setExpectedException('Exception');
        $product->save();
    }

    function testSaveIncreasesNumberOnNewProduct()
    {
        $product = $this->createProductObject();
        $name = 'Test';
        $price = 20;
        $product->getDetails()->Translation['da']->name = $name;
        $product->getDetails()->Translation['da']->description = '';
        $product->getDetails()->price = new Ilib_Variable_Float($price);

        try {
            $product->save();
        } catch (Exception $e) {
            $this->fail('Exception thrown '.$e->__toString());
        }

        $product->refresh(true);

        $this->assertEquals(1, $product->getDetails()->getNumber());
        $this->assertEquals($name, $product->getDetails()->getTranslation('da')->name);
        $this->assertEquals($price, $product->getDetails()->getPrice()->getAsIso());

        $product = $this->createProductObject();
        $name = 'Test 3';
        $price = 30;
        $product->getDetails()->Translation['da']->name = $name;
        $product->getDetails()->Translation['da']->description = '';
        $product->getDetails()->price = new Ilib_Variable_Float($price);

        try {
            $product->save();
        } catch (Exception $e) {
            $this->fail('Exception thrown '.$e->__toString());
        }

        $product->refresh(true);

        $this->assertEquals(2, $product->getDetails()->getNumber());
        $this->assertEquals($name, $product->getDetails()->getTranslation('da')->name);
        $this->assertEquals($price, $product->getDetails()->getPrice()->getAsIso());
    }

    function testSavePersistsItselfAndCanSaveAgainStillHavingTheSameNumber()
    {
        $product = $this->createProductObject();
        $name = 'Test';
        $price = 20;
        $product->getDetails()->Translation['da']->name = $name;
        $product->getDetails()->Translation['da']->description = '';
        $product->getDetails()->price = new Ilib_Variable_Float($price);

        try {
            $product->save();
        } catch (Exception $e) {
            $this->fail('Exception thrown '.$e->__toString());
        }
        $product->refresh(true);

        $name = 'Test 2';
        $product->getDetails()->Translation['da']->name = $name;
        try {
                $product->save();
        } catch (Exception $e) {
            $this->fail('Exception thrown '.$e->__toString());
        }
        $product->refresh(true);

        $this->assertEquals(1, $product->getDetails()->getNumber());
        $this->assertEquals($name, $product->getDetails()->getTranslation('da')->name);
        $this->assertEquals($price, $product->getDetails()->getPrice()->getAsIso());

    }


    function testSaveStateAccountIdInProductDetailsDoesntChangeOtherValues()
    {
        $product = $this->createProductObject();
        $name = 'Test';
        $price = 20;
        $product->getDetails()->Translation['da']->name = 'Test';
        $product->getDetails()->Translation['da']->description = '';
        $product->getDetails()->price = new Ilib_Variable_Float(20);
        $product->getDetails()->state_account_id = 10;

        try {
            $product->save();
        } catch (Exception $e) {
            $this->fail('Exception thrown '.$e->__toString());
        }

        $product->refresh(true);

        $id = $product->getId();
        $this->assertEquals(1, $product->getDetails()->getNumber());
        $this->assertEquals($name, $product->getDetails()->getTranslation('da')->name);
        $this->assertEquals($price, $product->getDetails()->getPrice()->getAsIso());
        $this->assertEquals(10, $product->getDetails()->getStateAccountId());


        $product = $this->createProductObject($id);
        $this->assertEquals($id, $product->getId());
        $this->assertTrue($product->getDetails()->setStateAccountId(20));

        $product->refresh(true);

        $this->assertEquals(1, $product->getDetails()->getNumber());
        $this->assertEquals($name, $product->getDetails()->getTranslation('da')->name);
        $this->assertEquals(20, $product->getDetails()->getStateAccountId());
    }

    function testChangeProductNameSavesAsNewDetail()
    {
        $product = $this->createProductObject();
        $product->getDetails()->Translation['da']->name = 'Test';
        $product->getDetails()->price = new Ilib_Variable_Float(20);

        try {
            $product->save();
        } catch (Exception $e) {
            $this->fail('Exception thrown '.$e->__toString());
        }

        $product->refresh(true);
        $this->assertEquals(1, $product->getDetails()->getId());
        $this->assertEquals('Test', $product->getDetails()->getTranslation('da')->name);

        $product->getDetails()->Translation['da']->name = 'Test 2';
        try {
            $product->save();
        } catch (Exception $e) {
            $this->fail('Exception thrown '.$e->__toString());
        }
        $product->refresh(true);
        // $product = $this->createProductObject($product->get('id'));

        $this->assertEquals(2, $product->getDetails()->getId());
        $this->assertEquals('Test 2', $product->getDetails()->getTranslation('da')->name);
    }

    function testChangeProductPriceSavesAsNewDetail()
    {
        $product = $this->createProductObject();
        $product->getDetails()->Translation['da']->name = 'Test';
        $product->getDetails()->price = new Ilib_Variable_Float(20);

        try {
            $product->save();
        } catch (Exception $e) {
            $this->fail('Exception thrown '.$e->__toString());
        }

        $product->refresh(true);
        $this->assertEquals(1, $product->getDetails()->getId());
        $this->assertEquals(20, $product->getDetails()->getPrice()->getAsIso());

        $product->getDetails()->price = new Ilib_Variable_Float(30);
        try {
            $product->save();
        } catch (Exception $e) {
            $this->fail('Exception thrown '.$e->__toString());
        }
        $product->refresh(true);
        // $product = $this->createProductObject($product->get('id'));

        $this->assertEquals(2, $product->getDetails()->getId());
        $this->assertEquals(30, $product->getDetails()->getPrice()->getAsIso());
    }
    
    function testDetailsIsNotUpdatedWhenErrorInTranslation()
    {
        $product = $this->createProductObject();
        $product->getDetails()->Translation['da']->name = 'Test';
        $product->getDetails()->price = new Ilib_Variable_Float(20);

        try {
            $product->save();
        } catch (Exception $e) {
            $this->fail('Exception thrown '.$e->__toString());
        }
        
        $product->refresh(true);
        $this->assertEquals(1, $product->getDetails()->getId());
        $this->assertEquals(20, $product->getDetails()->getPrice()->getAsIso());

        $product->getDetails()->Translation['da']->name = '';
        $product->getDetails()->price = new Ilib_Variable_Float(30);
        try {
            $product->save();
        } catch (Exception $e) {
            // we excpect error.
            // $this->fail('Exception thrown '.$e->__toString());
        }
        $product->refresh(true);
        // $product = $this->createProductObject($product->get('id'));

        $this->assertEquals(1, $product->getDetails()->getId());
        $this->assertEquals(20, $product->getDetails()->getPrice()->getAsIso());
    }

    public function testLoadEarlierDetails()
    {
        $product = $this->createProductObject();
        $product->getDetails()->Translation['da']->name = 'Test';
        $product->getDetails()->price = new Ilib_Variable_Float(20);

        try {
            $product->save();
        } catch (Exception $e) {
            $this->fail('Exception thrown '.$e->__toString());
        }

        $product->refresh(true);
        $this->assertEquals(1, $product->getDetails()->getId());
        $this->assertEquals('Test', $product->getDetails()->getTranslation('da')->name);

        $product->getDetails()->Translation['da']->name = 'Test 2';
        try {
            $product->save();
        } catch (Exception $e) {
            $this->fail('Exception thrown '.$e->__toString());
        }
        // $product->refresh(true);
        $product = $this->createProductObject(1);
        $this->assertEquals(1, $product->getDetails(1)->getId());
        $this->assertEquals('Test', $product->getDetails(1)->getTranslation('da')->name);
    }

    public function testShowInShop()
    {
        $product = $this->createProductObject();
        $product->getDetails()->Translation['da']->name = 'Test';
        $product->getDetails()->Translation['da']->description = '';
        $product->getDetails()->price = new Ilib_Variable_Float(20);
        $product->do_show = 1;
        try {
            $product->save();
        } catch (Exception $e) {
            foreach($product->getErrorStack() AS $field => $error) {
                echo $field; var_dump($error);
            }
        }

        $product = $this->createProductObject(1);
        $this->assertEquals(1, $product->showInShop());

    }
    
    function testSetAttributeGroupThrowsExceptionOnWhenNotSavedWithVariations()
    {
        $product = $this->createNewProduct();
        
        $group = new Intraface_modules_product_Attribute_Group;
        $group->name = 'Test1';
        $group->save();
        $group->load();
        
        try {
            $product->setAttributeGroup($group);
            $this->assertTrue(false, 'An excpetion is not thrown');
        }
        catch(Exception $e) {
            $this->assertEquals('You can not set attribute group for a product without variations!', $e->getMessage());
        }
    }

    function testSetAttributeGroup()
    {
        $product = $this->createNewProductWithVariations();
        
        $group = new Intraface_modules_product_Attribute_Group;
        $group->name = 'Test1';
        $group->save();
        $group->load();
        
        $this->assertTrue($product->setAttributeGroup($group));
    }
    
    function testGetAttributeGroups()
    {
        $product = $this->createNewProductWithVariations();

        $group = new Intraface_modules_product_Attribute_Group;
        $group->name = 'Test1';
        $group->save();
        $group->load();
        $product->setAttributeGroup($group);

        $group = new Intraface_modules_product_Attribute_Group;
        $group->name = 'Test2';
        $group->save();
        $group->load();
        $product->setAttributeGroup($group);

        $group = new Intraface_modules_product_Attribute_Group;
        $group->name = 'Test3';
        $group->save();
        $group->load();
        

        $expected = array(
            0 => array(
                'id' => 1,
                'intranet_id' => 1,
                'name' => 'Test1',
                '_old_deleted' => 0,
                'deleted_at' => NULL
            ),
            1 => array(
                'id' => 2,
                'intranet_id' => 1,
                'name' => 'Test2',
                '_old_deleted' => 0,
                'deleted_at' => NULL
            )
        );
        
        $groups = $product->getAttributeGroups();
        
        $this->assertEquals(2, $groups->count());
    }

    /*
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

    

    function testRemoveAttributeGroup()
    {
        $product = $this->createNewProductWithVariations();
        $product->setAttributeGroup(1);
        $this->assertTrue($product->removeAttributeGroup(1));
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
        require_once 'tests/unit/stubs/Fake/Intraface/modules/currency/Currency.php';
        $currency = new Fake_Intraface_modules_currency_Currency;
        require_once 'tests/unit/stubs/Fake/Intraface/modules/currency/Currency/ExchangeRate.php';
        $currency->product_price_exchange_rate = new Fake_Intraface_modules_currency_Currency_ExchangeRate;
        require_once 'tests/unit/stubs/Fake/Ilib/Variable/Float.php';
        $currency->product_price_exchange_rate->rate = new Fake_Ilib_Variable_Float;
        $currency->product_price_exchange_rate->rate->iso = 745.23;

        $product = new Product($this->kernel);
        $product->save(array('name' => 'test', 'price' => 200, 'unit' => 1));
        $product->load();

        $this->assertEquals(26.84, $product->getDetails()->getPriceInCurrency($currency)->getAsIso());



    }
    */
}
