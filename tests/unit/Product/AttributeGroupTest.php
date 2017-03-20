<?php
Intraface_Doctrine_Intranet::singleton(1);

class FakeAttributeGroupProduct
{
    public function getId()
    {
        return 1;
    }
}

class AttributeGroupTest extends PHPUnit_Framework_TestCase
{
    protected $db;

    function setUp()
    {
        $this->db = MDB2::singleton(DB_DSN);
    }

    function tearDown()
    {
        $this->db->query('TRUNCATE product_variation');
        $this->db->query('TRUNCATE product_variation_x_attribute');
        $this->db->query('TRUNCATE product_attribute');
        $this->db->query('TRUNCATE product_attribute_group');
    }

    function createGroups()
    {
        $group = new Intraface_modules_product_Attribute_Group;
        $group->name = 'color';
        $group->attribute[0]->name = 'red';
        $group->attribute[1]->name = 'blue';
        $group->attribute[2]->name = 'black';
        $group->attribute[3]->name = 'white';
        $group->save();


        $group = new Intraface_modules_product_Attribute_Group;
        $group->name = 'size';
        $group->attribute[0]->name = 'small';
        $group->attribute[1]->name = 'medium';
        $group->attribute[2]->name = 'large';
        $group->save();
    }


    ///////////////////////////////////////////////////////

    function testConstruct()
    {
        $object = new Intraface_modules_product_Attribute_Group;
        $this->assertTrue(is_object($object));
    }

    function testGetAttributesUsedOnProduct()
    {
        $this->createGroups();

        $j = 1;
        for ($a1 = 1; $a1 < 4; $a1++) {
            for ($a2 = 5; $a2 < 7; $a2++) {
                $variation = new Intraface_modules_product_Variation_TwoAttributeGroups;
                $variation->product_id = 1;
                $variation->number = $j;
                $variation->setAttributesFromArray(array('attribute1' => $a1, 'attribute2' => $a2));
                $variation->save();

                $j++;
            }
        }

        // Save an extra for another product
        $variation = new Intraface_modules_product_Variation_TwoAttributeGroups;
        $variation->product_id = 2;
        $variation->number = 10;
        $variation->setAttributesFromArray(array('attribute1' => 4, 'attribute2' => 7));
        $variation->save();

        $gateway = new Intraface_modules_product_Attribute_Group_Gateway;
        $group = $gateway->findById(1);

        $attributes = $group->getAttributesUsedByProduct(new FakeAttributeGroupProduct);
        $this->assertEquals(3, $attributes->count());

        $group = $gateway->findById(2);

        $attributes = $group->getAttributesUsedByProduct(new FakeAttributeGroupProduct);
        $this->assertEquals(2, $attributes->count());
    }
}
