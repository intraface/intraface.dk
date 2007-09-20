<?php
require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

require_once 'FileViewerTest.php';

class FileHandler_AllTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Intraface_FileHandler');

        $suite->addTestSuite('FileViewerTest');
        return $suite;
    }
}
?>