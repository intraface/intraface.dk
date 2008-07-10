<?php
require_once dirname(__FILE__) . '/../config.test.php';

Intraface_Doctrine_Intranet::singleton(1);

class VariationTwoAttributeGroupsTest extends PHPUnit_Framework_TestCase
{

    function setUp()
    {
        $db = MDB2::factory(DB_DSN);
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
        $object = new Intraface_modules_product_Variation_TwoAttributeGroups;
        $this->assertTrue(is_object($object));
    }
    
    function testSaveVariation()
    {
        $object = new Intraface_modules_product_Variation_TwoAttributeGroups;
        
        $input = array('attribute1' => 1,
            'attribute2' => 2);
        
        $object->product_id = 1;
        $object->setAttributesFromArray($input);
        
        $object->save();
        $object->load();
        $this->assertEquals(1, $object->getId());
            
    }
}
