<?php
require_once 'PHPUnit/TextUI/TestRunner.php';

class XMLRPC_AllTests
{
    public static function suite() {
        $suite = new PHPUnit_Framework_TestSuite('Intraface_XMLRPC');

        $tests = array('ContactXMLRPC', 'NewsletterXMLRPC');

        foreach ($tests AS $test) {
            require_once $test . 'Test.php';
            $suite->addTestSuite($test . 'Test');
        }

        return $suite;
    }
}
