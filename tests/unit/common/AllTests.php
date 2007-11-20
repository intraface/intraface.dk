<?php
require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

class Common_AllTests {

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Intraface_Common');

        $tests = array('Auth', 'Kernel', 'Module', 'Setting', 'User', 'Weblogin', 'Redirect', 'DBQuery', 'Error', 'Position', 'Intranet', 'Address', 'Amount', 'Validator');

        foreach ($tests AS $test) {
            require_once $test . 'Test.php';
            $suite->addTestSuite($test . 'Test');
        }

        return $suite;
    }
}
?>