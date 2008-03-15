<?php
require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

class Accounting_AllTests extends PHPUnit_Framework_TestSuite
{

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Intraface_Accounting');

        $tests = array('Account', 'Voucher', 'Year', 'VatPeriod', 'YearEnd');

        foreach ($tests AS $test) {
            require_once $test . 'Test.php';
            $suite->addTestSuite($test . 'Test');
        }

        return $suite;
    }
}
?>