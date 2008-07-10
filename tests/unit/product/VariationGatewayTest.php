<?php
require_once dirname(__FILE__) . '/../config.test.php';

Intraface_Doctrine_Intranet::singleton(1);

class FakeVariationGatewayProduct {
    
    private $groups;
    
    public function __construct($groups) {
        $this->groups = $groups;
    }
    
    public function getId()
    {
        return 1;
    }
    
    public function getAttributeGroups()
    {
        return $this->groups;
    }
    
    public function get($key)
    {
        $values = array('has_variation' => 1);
        return $values[$key];
    }
}


class VariationGatewayTest extends PHPUnit_Framework_TestCase
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
        $object = new Intraface_modules_product_Variation_Gateway(new FakeVariationGatewayProduct(1));
        $this->assertTrue(is_object($object));
    }
    
    
    
    
}
