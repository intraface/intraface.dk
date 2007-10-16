<?php
require_once dirname(__FILE__) . '/../config.test.php';

require_once 'PHPUnit/Framework.php';
require_once 'Intraface/Standard.php';
require_once 'Intraface/Kernel.php';
require_once 'Intraface/shared/filehandler/FileHandler.php';
require_once 'Intraface/shared/filehandler/AppendFile.php';

class FakeFileHandler
{
    function get()
    {
        return 1;
    }
}

class FakeAppendFileIntranet
{
    function get() { return 1; }
}

class AppendFileTest extends PHPUnit_Framework_TestCase
{
    function createAppendFile()
    {
        $kernel = new Kernel;
        $kernel->intranet = new FakeAppendFileIntranet;
        return new AppendFile($kernel, 'product', 1);
    }

    /////////////////////////////////////////////////////////

    function testConstruction()
    {
        $append = $this->createAppendFile();
        $this->assertTrue(is_object($append));
    }

    function testAddFileAsInteger()
    {
           $append = $this->createAppendFile();
           $this->assertTrue($append->addFile(1));
    }

    function testAddFileAsArray()
    {
           $append = $this->createAppendFile();
           $this->assertTrue($append->addFile(array(1)));
    }

}
?>