<?php
require_once 'PHPUnit/TextUI/TestRunner.php';
require_once 'ModuleMaintenanceTest.php';

class Intranetmaintenance_AllTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Intraface_Intranetmaintenance');

        $tests = array('ModuleMaintenance', 'IntranetMaintenance');

        foreach ($tests AS $test) {
            require_once $test . 'Test.php';
            $suite->addTestSuite($test . 'Test');
        }

        return $suite;
    }
}
