<?php
require_once dirname(__FILE__) . '/../config.test.php';

class ModuleHandlerIntranet
{
    function hasModuleAccess()
    {
    	return true;
    }
}

class ModuleHandlerTest extends PHPUnit_Framework_TestCase
{
    private $handler;

    function setUp()
    {
        $this->handler = new Intraface_ModuleHandler(new ModuleHandlerIntranet);
    }

    function testUseModuleThrowsAnExceptionIfTheModuleIsNotValid()
    {
        try {
            $this->handler->useModule('invalid module name');
            $this->assertTrue(false, 'Exception should have been thrown');
        } catch (Exception $e) {
            $this->assertTrue(true);
        }
    }

    function testSetPrimaryModuleThrowsAnExceptionWhenNoIntranetIsset()
    {
        try {
            $this->handler->setPrimaryModule('intranetmaintenance');
            $this->assertFalse(true, 'Should have thrown an exception');
        } catch (Exception $e) {
            $this->assertTrue(true);
        }
    }

    function testUseModuleReturnsTheModuleAsAnObjectTrueWhenModuleIsAvailable()
    {
        $this->assertTrue(is_object($module = $this->handler->useModule('intranetmaintenance')));
        $this->assertEquals('intranetmaintenance', $module->getName());
    }

    function testGetModule()
    {
        $this->assertTrue(is_object($this->handler->useModule('intranetmaintenance')));
        $this->assertTrue(is_object($module = $this->handler->getModule('intranetmaintenance')));
        $this->assertEquals('intranetmaintenance', $module->getName());

    }

    function testGetModules()
    {
        $db = MDB2::singleton(DB_DSN);
        $result = $db->query('SELECT * FROM module');
        if (PEAR::isError($result)) {
            die($result->getMessage() . $result->getUserInfo());
        }

        $this->assertTrue(is_array($this->handler->getModules(MDB2::singleton(DB_DSN))));
        $this->assertEquals($result->numRows(), count($this->handler->getModules(MDB2::singleton(DB_DSN))));
    }

}

