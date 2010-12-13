<?php
require_once 'PHPUnit/TextUI/TestRunner.php';
require_once 'FileManagerTest.php';

class FileManager_AllTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Intraface_FileManager');

        $suite->addTestSuite('FileManagerTest');
        return $suite;
    }
}
