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
    function setUp()
    {
        $db = MDB2::factory(DB_DSN);
        $db->query('TRUNCATE filehandler_append_file');
    }

    function createAppendFile($id = 0)
    {
        $kernel = new Kernel;
        $kernel->intranet = new FakeAppendFileIntranet;
        return new AppendFile($kernel, 'product', 1, $id);
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

    function testDeleteReturnsTrue()
    {
        $append = $this->createAppendFile();
        $append->addFile(1);
        $this->assertTrue($append->delete(1));
    }

    function testUnDeleteReturnsTrue()
    {
        $append = $this->createAppendFile();
        $append->addFile(1);
        $append->delete(1);
        $this->assertTrue($append->undelete(1));
    }


}
?>