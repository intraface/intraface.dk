<?php
require_once 'Intraface/Kernel.php';
require_once 'Intraface/modules/intranetmaintenance/ModuleMaintenance.php';

class ModuleMaintenanceTest extends PHPUnit_Framework_TestCase
{
    function createModuleMaintenance()
    {
        return new ModuleMaintenance;
    }

    function testCreateModuleMaintenance()
    {
        $modulemaintain = $this->createModuleMaintenance();
        $this->assertTrue(is_object($modulemaintain));
    }

    function testRegisterModule()
    {
        $modulemaintain = $this->createModuleMaintenance();
        $return = $modulemaintain->registerModule('accounting');
        $this->assertTrue(is_array($return));
    }

    function testModuleMaintenanceFactory()
    {

        $kernel = new Intraface_Kernel;
        $object = ModuleMaintenance::factory('accounting');

        $this->assertTrue(is_object($object));
        $this->assertEquals('modulemaintenance', strtolower(get_class($object)));


    }
}
