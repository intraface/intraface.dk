<?php
require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

class Debtor_AllTests
{

    public static function suite() {
        $suite = new PHPUnit_Framework_TestSuite('Intraface_Debtor');

        $tests = array('Debtor', 'DebtorItem', 'DebtorPdf', 'Invoice', 'CreditNote', 'Reminder', 'Payment', 'Depreciation');

        foreach ($tests AS $test) {
            require_once $test . 'Test.php';
            $suite->addTestSuite($test . 'Test');
        }
        
        return $suite;
    }
}
?>