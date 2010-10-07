<?php
require_once dirname(__FILE__) . '/../config.test.php';

Intraface_Doctrine_Intranet::singleton(1);

class ShopGatewayTest extends PHPUnit_Framework_TestCase
{
    private $gateway;
    protected $backupGlobals = FALSE;

    function setUp()
    {
        $db = MDB2::singleton(DB_DSN);
        $result = $db->query('TRUNCATE shop');


        // $this->kernel = new Stub_Kernel;
        $this->gateway = new Intraface_modules_shop_Shop_Gateway;
        
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
    
    function testFindAll()
    {
        $id = $this->createShop();
        $id = $this->createShop();
        Intraface_Doctrine_Intranet::singleton(2);
        $id = $this->createShop();
        Intraface_Doctrine_Intranet::singleton(1);
        
        $collection = $this->gateway->findAll();
        $this->assertEquals(2, $collection->count());
    }

}