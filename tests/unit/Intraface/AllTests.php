<?php
require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

class Intraface_AllTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Intraface_Contact');

        $tests = array('Address');

        foreach ($tests AS $test) {
            require_once $test . 'Test.php';
            $suite->addTestSuite($test . 'Test');
        }

        return $suite;
    }
}
?>