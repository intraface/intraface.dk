<?php
require_once dirname(__FILE__) . '/../config.test.php';

Intraface_Doctrine_Intranet::singleton(1);

class FakeVariationDetailTestProduct
{
    function getAttributeGroups()
    {
        return array('some data');
    }
    
    function hasVariation()
    {
        return 1;
    }
    
    function getId()
    {
        return 1;
    }
}

class VariationDetailTest extends PHPUnit_Framework_TestCase
{

    private $object = NULL;
    
    function setUp()
    {
        $db = MDB2::singleton(DB_DSN);
        $db->query('TRUNCATE product_variation');
        $db->query('TRUNCATE product_variation_detail');
        $db->query('TRUNCATE product_attribute');
        $db->query('TRUNCATE product_attribute_group');
        
        
    }
    
    function createVariation() 
    {
        if ($this->object === NULL) {
            $group = new Intraface_modules_product_Attribute_Group;
            $group->name = 'color';
            $group->attribute[0]->name = 'red';
            $group->save();
            
            $object = new Intraface_modules_product_Variation_OneAttributeGroup;
            $object->product_id = 1;
            $object->setAttributesFromArray(array('attribute1' => 1));
            $object->save();
            return $this->object = $object;
        }
        
        $gateway = new Intraface_modules_product_Variation_Gateway(new FakeVariationDetailTestProduct);
        return $gateway->findById(1);
        
        // return Doctrine::getTable('Intraface_modules_product_Variation_OneAttributeGroup')->find(1);
    }
    
    ///////////////////////////////////////////////////////
    
    function testConstruct()
    {
        $object = new Intraface_modules_product_Variation_Detail;
        $this->assertTrue(is_object($object));
    }
    
    function testSaveDetail()
    {
        $object = new Intraface_modules_product_Variation_Detail;
        $object->product_variation_id = 1;
        $object->price_difference = -20;
        $object->weight_difference = 20;
        $object->save();
        $object->load();
        $this->assertEquals(1, $object->getId());
            
    }
    
    function testSaveDetailFromVariation()
    {
        $object = new Intraface_modules_product_Variation_OneAttributeGroup;
        $object->product_id = 1;
        $object->setAttributesFromArray(array('attribute1' => 1));
        $object->detail[0]->price_difference = -20;
        $object->detail[0]->weight_difference = 20;
        
        $object->save();
        $object->load();
        $this->assertEquals(1, $object->getId());
        $this->assertEquals(1, $object->detail->count());
            
    }
    
    function testGetDetailReturnsEmptyRecordOnNoDetails()
    {
        $object = $this->createVariation();
        $detail = $object->getDetail();
        $this->assertEquals('Intraface_modules_product_Variation_Detail', get_class($detail));
        $detail->price_difference = 0;
        $detail->weight_difference = 0;
        $detail->save();
        $this->assertEquals(1, $detail->getId());
            
    }
    
    function testSaveDetailDoesNotSaveOnSameDetails()
    {
        $object = $this->createVariation();
        $detail = $object->getDetail();
        $detail->price_difference = 10;
        $detail->weight_difference = 10;
        $detail->save();
        
        $object = $this->createVariation();
        $detail = $object->getDetail();
        $detail->price_difference = 10;
        $detail->weight_difference = 10;
        $detail->save();
        $detail->refresh();
        $this->assertEquals(1, $detail->getId());    
    }
    
    function testSaveDetailSavesNewOnNewDetails()
    {
        $object = $this->createVariation();
        $detail = $object->getDetail();
        $detail->price_difference = 10;
        $detail->weight_difference = 10;
        $detail->save();
        sleep(1); // Puts a second difference in save time.
        
        $object = $object = $this->createVariation();
        $detail = $object->getDetail();
        $detail->price_difference = 20;
        $detail->weight_difference = 10;
        $detail->save();
        
        $object = $object = $this->createVariation();
        // $object->refresh(true);
        
        $detail = $object->getDetail();
        $this->assertEquals(2, $detail->getId());
        $this->assertEquals(20, $detail->getPriceDifference());
    }
}
