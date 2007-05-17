<?php
if (!defined('PHPUNIT_MAIN_METHOD')) {
    define('PHPUNIT_MAIN_METHOD', 'IntranetmaintenanceTests::main');
}

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

require_once 'ModuleMaintenanceTest.php';

class IntranetmaintenanceTests
{

    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Intranetmaintenace');

        $suite->addTestSuite('ModuleMaintenanceTest');

        return $suite;
    }
}

if (PHPUNIT_MAIN_METHOD == 'IntranetmaintenanceTests::main') {
    IntranetmaintenanceTests::main();
}
?>