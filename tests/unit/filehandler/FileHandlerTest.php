<?php
require_once dirname(__FILE__) . '/../config.test.php';

require_once 'PHPUnit/Framework.php';
require_once 'Intraface/Standard.php';
require_once 'Intraface/Kernel.php';
require_once 'Intraface/shared/filehandler/FileHandler.php';

class FakeFileHandlerIntranet
{
    function get()
    {
        return 1;
    }
}

class FileHandlerTest extends PHPUnit_Framework_TestCase
{
    function createFileHandler()
    {
        $kernel = new Kernel;
        $kernel->intranet = new FakeFileHandlerIntranet;
        return new FileHandler($kernel);
    }

    function testConstruction()
    {
        $filehandler = $this->createFileHandler();
        $this->assertTrue(is_object($filehandler));
    }

}
?>