<?php
require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';
require_once 'DB/Sql.php';

class Common_AllTests {

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Intraface_Common_Tests');

        $tests = array(
           	'Address',
            'Amount',
            'Date',
            'DBQuery',
            'Error',
            'Intranet',
            'Kernel',
            'ModuleHandler',
            'Redirect',
            'Setting',
            'User',
            'Validator');

        foreach ($tests AS $test) {
            require_once $test . 'Test.php';
            $suite->addTestSuite($test . 'Test');
        }

        return $suite;
    }
}