<?php
require_once dirname(__FILE__) . '/../config.test.php';
require_once 'PHPUnit/Framework.php';

require_once 'Intraface/modules/intranetmaintenance/ModuleMaintenance.php';
require_once 'Intraface/Kernel.php';


class ModuleMaintenanceTest extends PHPUnit_Framework_TestCase
{
    function createModuleMaintenance()
    {
        $kernel = new Kernel;
        return new ModuleMaintenance($kernel);
    }

    function testCreateModuleMaintenance()
    {
        $modulemaintain = $this->createModuleMaintenance();
        $this->assertTrue(is_object($modulemaintain));
    }
}
?>