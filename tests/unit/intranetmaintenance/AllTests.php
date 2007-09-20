<?php
require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

require_once 'ModuleMaintenanceTest.php';

class Intranetmaintenance_AllTests
{

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Intraface_Intranetmaintenance');

        $suite->addTestSuite('ModuleMaintenanceTest');

        return $suite;
    }
}
?>