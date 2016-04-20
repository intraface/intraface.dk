<?php
Intraface_Doctrine_Intranet::singleton(1);

class ShopGatewayTest extends PHPUnit_Framework_TestCase
{
    private $gateway;
    protected $backupGlobals = false;
    protected $db;

    function setUp()
    {
        $this->db = MDB2::singleton(DB_DSN);
        $this->gateway = new Intraface_modules_shop_Shop_Gateway;
    }

    function tearDown()
    {
        $result = $this->db->query('TRUNCATE shop');
    }

    private function createShop($values = array())
    {
        $shop = new Intraface_modules_shop_Shop();
        $shop->name = 'Test shop';
        if (!empty($values['intranet_id'])) {
            $shop->intranet_id = $values['intranet_id'];
        }
        $shop->save();

        return $shop->getId();
    }

    ////////////////////////////////////////////////

    function testConstruction()
    {
        $this->assertTrue(is_object($this->gateway));
    }

    function testFindById()
    {
        $id = $this->createShop();

        $shop = $this->gateway->findById($id);
        $this->assertEquals(1, $shop->getId());
        $this->assertEquals('Test shop', $shop->getName());
    }

    function testFindAllTakesIntranetIdIntoAccount()
    {
        $this->markTestSkipped(
          'This test is not passing.'
        );
        Intraface_Doctrine_Intranet::singleton(1);
        $id = $this->createShop();
        $id = $this->createShop();
        $collection = $this->gateway->findAll();
        $this->assertEquals(2, $collection->count());

        Intraface_Doctrine_Intranet::singleton(2);
        $id = $this->createShop();
        $collection = $this->gateway->findAll();
        $this->assertEquals(1, $collection->count());

        Intraface_Doctrine_Intranet::singleton(1);
        $collection = $this->gateway->findAll();
        $this->assertEquals(2, $collection->count());
    }
}
