<?php
require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

require_once 'DebtorPdfTest.php';

class Debtor_AllTests
{

    public static function suite() {
        $suite = new PHPUnit_Framework_TestSuite('Intraface_Debtor');

        $suite->addTestSuite('DebtorPdfTest');
        return $suite;
    }
}
?>