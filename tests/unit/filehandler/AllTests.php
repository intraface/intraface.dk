<?php
require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

require_once 'FileViewerTest.php';
require_once 'FileHandlerTest.php';
require_once 'AppendFileTest.php';

class FileHandler_AllTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Intraface_FileHandler');

        $suite->addTestSuite('FileViewerTest');
        $suite->addTestSuite('FileHandlerTest');
        $suite->addTestSuite('AppendFileTest');
        return $suite;
    }
}
?>