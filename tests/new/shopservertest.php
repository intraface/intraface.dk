<?php
require_once dirname(__FILE__) . '/../config.test.php';

require_once 'PHPUnit/Framework.php';
require_once 'Intraface/XMLRPC/Shop/Server.php';
require_once 'Intraface/modules/intranetmaintenance/ModuleMaintenance.php';
require_once 'Intraface/modules/intranetmaintenance/IntranetMaintenance.php';
require_once 'Intraface/Kernel.php';


class ShopServerTest extends PHPUnit_Framework_TestCase
{
    private $credentials;

    function __construct()
    {
        $install = new Install;
        $install->resetServer();
        $this->credentials = array(
            'private_key' => 'privatekeyshouldbereplaced',
            'session_id' => rand(1, 10000000)
        );

        $modules = array('webshop', 'debtor', 'order', 'contact', 'product');
        $intranet_id = 1;

        $moduleadmin = new ModuleMaintenance(new Kernel);
        $intranetadmin = new IntranetMaintenance(new Kernel, $intranet_id);

        foreach ($modules AS $module) {
            $moduleadmin->registerModule($module);
            $intranetadmin->setModuleAccess($module);
        }

    }

    function setUp()
    {

    }

    function createServer()
    {
        return new Intraface_XMLRPC_Shop_Server();
    }

    function testCreateClient()
    {
        $client = $this->createServer();
        $this->assertEquals(get_class($client), 'Intraface_XMLRPC_Shop_Server');
    }

    function testCheckCredentials()
    {
        $client = $this->createServer();
        $this->assertTrue($client->checkCredentials($this->credentials));
        $this->assertTrue(is_object($client->kernel));
        $this->assertTrue(is_object($client->kernel->intranet));
        $this->assertTrue(is_object($client->kernel->setting));
    }

    function _testGetProduct()
    {
        $product_id = 6683;
        $client = $this->createClient();
        $product = $client->getProduct($product_id);
        $this->assertTrue(gettype($product) == 'array');
    }

    function testGetProducts()
    {
        $server = $this->createServer();
        $this->assertTrue(gettype($server->getProducts($this->credentials)) == 'array');
    }

}
?>