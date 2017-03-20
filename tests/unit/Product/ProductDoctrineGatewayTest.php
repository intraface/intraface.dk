<?php
Intraface_Doctrine_Intranet::singleton(1);

class ProductDoctrineGatewayTest extends PHPUnit_Framework_TestCase
{
    protected $connection;

    function setUp()
    {
        $this->connection = Doctrine_Manager::connection();
        $this->connection->clear(); // clear repo, so that we are sure data are loaded again.
    }

    function tearDown()
    {
        $this->connection->exec('TRUNCATE product');
        $this->connection->exec('TRUNCATE product_attribute');
        $this->connection->exec('TRUNCATE product_attribute_group');
        $this->connection->exec('TRUNCATE product_detail');
        $this->connection->exec('TRUNCATE product_detail_translation');
        $this->connection->exec('TRUNCATE product_variation');
        $this->connection->exec('TRUNCATE product_variation_detail');
        $this->connection->exec('TRUNCATE product_variation_x_attribute');
        $this->connection->exec('TRUNCATE product_x_attribute_group');

        $this->connection->clear();
    }

    function createProductObject($id = 0)
    {
        if ($id != 0) {
            $this->connection->clear(); // clear repo, so that we are sure data are loaded again.
            $gateway = new Intraface_modules_product_ProductDoctrineGateway($this->connection, null);
            return $gateway->findById($id);
        }
        return new Intraface_modules_product_ProductDoctrine;
    }

    function createProduct($name)
    {
        $product = $this->createProductObject();
        $product->getDetails()->Translation['da']->name = $name;
        $product->getDetails()->Translation['da']->description = '';
        $product->getDetails()->price = new Ilib_Variable_Float(20);
        $product->getDetails()->unit = 1;
        $product->has_variation = 1;
        $product->save();
        $product->refresh(true);
        return $product;
    }

    public function createAttribute($group_name, $attributes)
    {
        $group = new Intraface_modules_product_Attribute_Group;
        $group->name = $group_name;
        foreach ($attributes as $attribute) {
            $group->attribute[0]->name = $attribute;
        }
        $group->save();

        return $group;
    }

    function createGateway()
    {
        return new Intraface_modules_product_ProductDoctrineGateway($this->connection, null);
    }

    public function testFindByAttribute()
    {
        $attribute1 = $this->createAttribute('color', array('blue', 'red'));
        $attribute2 = $this->createAttribute('size', array('small', 'medium'));

        $product1 = $this->createProduct('product1');
        $product1->setAttributeGroup($attribute1);
        $product1->setAttributeGroup($attribute2);

        $variation1 = $product1->getVariation();
        $variation1->setAttributesFromArray(array('attribute1' => 1, 'attribute2' => 3)); // blue and small
        $variation1->save();

        $detail = $variation1->getDetail();
        $detail->save();

        $gateway = $this->createGateway();
        $products = $gateway->findByVariationAttributeId(3); // blue;

        $this->assertEquals(1, $products->count());
    }

    /*
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
    */
}
