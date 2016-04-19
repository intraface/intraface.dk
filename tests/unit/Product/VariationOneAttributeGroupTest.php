<?php
Intraface_Doctrine_Intranet::singleton(1);

class VariationOneAttributeGroupTest extends PHPUnit_Framework_TestCase
{

    function setUp()
    {
        $db = MDB2::singleton(DB_DSN);
        $db->query('TRUNCATE product_variation');
        $db->query('TRUNCATE product_variation_x_attribute');
        $db->query('TRUNCATE product_attribute');
        $db->query('TRUNCATE product_attribute_group');
        
    }
    
    function createGroups()
    {
        $group = new Intraface_modules_product_Attribute_Group;
        $group->name = 'color';
        $group->attribute[0]->name = 'red';
        $group->attribute[1]->name = 'blue';
        $group->save();
        
        
        $group = new Intraface_modules_product_Attribute_Group;
        $group->name = 'size';
        $group->attribute[0]->name = 'small';
        $group->attribute[1]->name = 'medium';
        $group->save();
    }
    
    
    ///////////////////////////////////////////////////////
    
    function testConstruct()
    {
        $object = new Intraface_modules_product_Variation_OneAttributeGroup;
        $this->assertTrue(is_object($object));
    }
    
    function testSaveVariation()
    {
        $object = new Intraface_modules_product_Variation_OneAttributeGroup;
        
        $input = array('attribute1' => 1);
        
        $object->product_id = 1;
        $object->setAttributesFromArray($input);
        
        $object->save();
        $object->load();
        $this->assertEquals(1, $object->getId());
            
    }
    
    function testSaveVariationIncrementsNumber()
    {
        $object = new Intraface_modules_product_Variation_OneAttributeGroup;
        $input = array('attribute1' => 1);
        $object->product_id = 1;
        $object->setAttributesFromArray($input);
        $object->save();
        $object->load();
        $this->assertEquals(1, $object->getNumber());
        
        $object = new Intraface_modules_product_Variation_OneAttributeGroup;
        $input = array('attribute1' => 1);
        $object->product_id = 1;
        $object->setAttributesFromArray($input);
        $object->save();
        $object->load();
        $this->assertEquals(2, $object->getNumber());
            
    }
    
    function testSaveVariationIncrementNumberAlsoUsesDeleted()
    {
        $object = new Intraface_modules_product_Variation_OneAttributeGroup;
        $input = array('attribute1' => 1);
        $object->product_id = 1;
        $object->setAttributesFromArray($input);
        $object->save();
        $object->load();
        $this->assertEquals(1, $object->getNumber());
        
        
        $object = new Intraface_modules_product_Variation_OneAttributeGroup;
        $input = array('attribute1' => 1);
        $object->product_id = 1;
        $object->setAttributesFromArray($input);
        $object->save();
        $object->load();
        $this->assertEquals(2, $object->getNumber());
        $object->delete();
        
        $object = new Intraface_modules_product_Variation_OneAttributeGroup;
        $input = array('attribute1' => 1);
        $object->product_id = 1;
        $object->setAttributesFromArray($input);
        $object->save();
        $object->load();
        $this->assertEquals(3, $object->getNumber());
        
    }
}
