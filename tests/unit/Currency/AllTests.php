<?php
require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

class Currency_AllTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Intraface_Currency');

        $tests = array('Currency', 'ExchangeRate');

        foreach ($tests AS $test) {
            require_once $test . 'Test.php';
            $suite->addTestSuite($test . 'Test');
        }

        return $suite;
    }
}
?>