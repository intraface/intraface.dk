<?php
require_once 'PHPUnit/TextUI/TestRunner.php';

require_once 'OnlinePaymentTest.php';

class OnlinePayment_AllTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Intraface_Onlinepayment');

        $suite->addTestSuite('OnlinePaymentTest', 'LanguageTest');
        return $suite;
    }
}