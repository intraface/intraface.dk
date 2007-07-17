<?php
if (!defined('PHPUNIT_MAIN_METHOD')) {
    define('PHPUNIT_MAIN_METHOD', 'CommonTests::main');
}

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

class CommonTests {
    public static function main() {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite() {
        $suite = new PHPUnit_Framework_TestSuite('Common');

        $tests = array('Auth', 'Kernel', 'Module', 'Setting', 'User', 'Weblogin', 'Redirect', 'DBQuery', 'Error', 'Position');

        foreach ($tests AS $test) {
            require_once $test . 'Test.php';
            $suite->addTestSuite($test . 'Test');
        }

        return $suite;
    }
}

if (PHPUNIT_MAIN_METHOD == 'CommonTests::main') {
    CommonTests::main();
}
?>