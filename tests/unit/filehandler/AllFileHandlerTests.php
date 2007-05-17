<?php
if (!defined('PHPUNIT_MAIN_METHOD')) {
    define('PHPUNIT_MAIN_METHOD', 'FileHandlerTests::main');
}

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

require_once 'FileViewerTest.php';

class FileHandlerTests {
    public static function main() {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite() {
        $suite = new PHPUnit_Framework_TestSuite('FileManager');

        $suite->addTestSuite('FileViewerTest');
        return $suite;
    }
}

if (PHPUNIT_MAIN_METHOD == 'FileHandlerTests::main') {
    FileHandlerTests::main();
}
?>