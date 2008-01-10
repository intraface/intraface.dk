<?php
require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

require_once 'DebtorTest.php';
require_once 'DebtorItemTest.php';
require_once 'DebtorPdfTest.php';

class Debtor_AllTests
{

    public static function suite() {
        $suite = new PHPUnit_Framework_TestSuite('Intraface_Debtor');

        $suite->addTestSuite('DebtorTest');
        $suite->addTestSuite('DebtorItemTest');
        $suite->addTestSuite('DebtorPdfTest');
        $suite->addTestSuite('DebtorPaymentTest');
        return $suite;
    }
}
?>