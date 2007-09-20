<?php
require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

require_once 'pdf/PdfMakerTest.php';

class Shared_AllTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Intraface_Shared');

        $suite->addTestSuite('PdfMakerTest');
        return $suite;
    }
}
?>