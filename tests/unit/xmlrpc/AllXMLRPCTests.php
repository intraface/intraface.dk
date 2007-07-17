<?php
if (!defined('PHPUNIT_MAIN_METHOD')) {
    define('PHPUNIT_MAIN_METHOD', 'XMLRPCTests::main');
}

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

class XMLRPCTests {
    public static function main() {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite() {
        $suite = new PHPUnit_Framework_TestSuite('XMLRPC');

        $tests = array('CMS', 'Contact', 'Newsletter', 'Shop');

        foreach ($tests AS $test) {
            require_once $test . 'ServerTest.php';
            $suite->addTestSuite($test . 'ServerTest');
        }

        return $suite;
    }
}

if (PHPUNIT_MAIN_METHOD == 'XMLRPCTests::main') {
    XMLRPCTests::main();
}
?>