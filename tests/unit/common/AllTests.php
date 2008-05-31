<?php
require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

class Common_AllTests {

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Intraface_Common');

        $tests = array('Kernel', 'ModuleHandler', 'Setting', 'User', 'Redirect', 'DBQuery', 'Error', 'Intranet', 'Address', 'Amount', 'Validator');

        foreach ($tests AS $test) {
            require_once $test . 'Test.php';
            $suite->addTestSuite($test . 'Test');
        }

        return $suite;
    }
}
?>