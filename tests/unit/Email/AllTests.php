<?php
require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

require_once 'EmailTest.php';

class Email_AllTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Intraface_Email');

        $suite->addTestSuite('EmailTest');
        return $suite;
    }
}
?>