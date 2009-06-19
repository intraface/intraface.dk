<?php
require_once dirname(__FILE__) . '/../config.test.php';

class FakeShopFeaturedProductsIntranet
{
    function getId()
    {
        return 1;
    }
}

class FakeShopFeaturedProductsKeyword
{
    function getId()
    {
        return 1;
    }
}

class ShopFeaturedProductsTest extends PHPUnit_Framework_TestCase
{
    private $featured;
    protected $backupGlobals = FALSE;

    function setUp()
    {
        $db = MDB2::factory(DB_DSN);
        $db->exec('TRUNCATE shop_featuredproducts');
        $this->featured = new Intraface_modules_shop_FeaturedProducts(new FakeShopFeaturedProductsIntranet, new FakeShopFeaturedProductsIntranet, $db);
    }

    function testConstruction()
    {
        $this->assertTrue(is_object($this->featured));
    }

    function testAddReturnsTrueAndPersistsTheAddedStuff()
    {
        $this->assertTrue($this->featured->add('Test', new FakeShopFeaturedProductsKeyword));
        $this->assertTrue(is_array($this->featured->getAll()));
        $this->assertEquals(1, count($this->featured->getAll()));
    }

    function testAddOnlyPersistsOneOfEachKeyword()
    {
        $this->assertTrue($this->featured->add('Test', new FakeShopFeaturedProductsKeyword));
        $this->assertTrue($this->featured->add('Test', new FakeShopFeaturedProductsKeyword));

        $this->assertEquals(1, count($this->featured->getAll()));
    }

    function testDeleteActuallyDeletesTheFeaturedProductKeyword()
    {
        $this->assertTrue($this->featured->add('Test', new FakeShopFeaturedProductsKeyword));
        $this->assertTrue($this->featured->delete(1));
        $this->assertEquals(0, count($this->featured->getAll()));
    }

}
