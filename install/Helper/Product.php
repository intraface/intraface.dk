<?php
class Install_Helper_Product {

    private $kernel;
    private $db;

    public function __construct($kernel, $db) {
        $this->kernel = $kernel;
        $this->db = $db;

        Intraface_Doctrine_Intranet::singleton(1);
    }

    public function create()
    {
        require_once 'Intraface/modules/product/Product.php';
        $product = new Product($this->kernel);

        return $product->save(array('name' => 'Product 1', 'price' => 100, 'unit' => 1));
    }

    public function createVisibleInShop()
    {
        require_once 'Intraface/modules/product/Product.php';
        $product = new Product($this->kernel);
        $product->save(array('name' => 'Product 1', 'price' => '100,10', 'unit' => 1, 'do_show' => 1));

        return $product;
    }

    public function createVisibleInShopForPaging()
    {
        for ($i = 0; $i <= 20; $i++) {
            $this->createVisibleInShop();
        }
    }

    public function createVisibleInShopWithCategories()
    {
        $product = $this->createVisibleInShop();

        require 'Shop.php';
        $shop = new Install_Helper_Shop($this->kernel, $this->db);

        $category = $shop->createCategory('Category 1', 'category1', 0);
        $appender = $category->getAppender($product->get('id'));

        $category = $shop->createCategory('Category 2', 'category2', 1);
        $appender->add($category);

        $category = $shop->createCategory('Category 3', 'category3', 0);
        $appender->add($category);

        return $product;
    }

    public function createAttributes()
    {
        $group = new Intraface_modules_product_Attribute_Group;

        $group->name = 'Size';
        $group->attribute[0]->name = 'Small';
        $group->attribute[0]->position = 1;
        $group->attribute[1]->name = 'Medium';
        $group->attribute[1]->position = 2;
        $group->attribute[2]->name = 'Large';
        $group->attribute[2]->position = 3;
        $group->save();

        $group = new Intraface_modules_product_Attribute_Group;

        $group->name = 'Color';
        $group->attribute[0]->name = 'Blue';
        $group->attribute[0]->position = 1;
        $group->attribute[1]->name = 'Black';
        $group->attribute[1]->position = 2;
        $group->attribute[2]->name = 'Red';
        $group->attribute[2]->position = 3;
        $group->save();
    }

    public function createWithVariations()
    {
        $this->createAttributes();
        require_once 'Intraface/modules/product/Product.php';
        $product = new Product($this->kernel);

        $product->save(array('name' => 'Product 1', 'price' => 100, 'unit' => 2, 'has_variation' => 1, 'do_show' => 1, 'weight' => 110));
        $product->setAttributeGroup(1);
        $product->setAttributeGroup(2);
        foreach (array(1, 2, 3) AS $a1) {
            foreach (array(4, 5, 6) AS $a2) {
                $variation = $product->getVariation();
                $variation->setAttributesFromArray(array('attribute1' => $a1, 'attribute2' => $a2));
                $variation->save();
                $variation->load();
                $detail = $variation->getDetail();
                $detail->price_difference = 0; /* Can be reimplemented: ($a1 * $a2); */
                $detail->weight_difference = -1*($a1 * $a2);
                $detail->save();

            }

        }
    }
}
