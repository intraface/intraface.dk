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

class FakeAppendFileFile
{
    function getId()
    {
        return 1;
    }
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
        $this->assertTrue($append->addFile(new FakeAppendFileFile) > 0);
    }

    function testAddFilesAsArray()
    {
        $append = $this->createAppendFile();
        $this->assertTrue($append->addFiles(array(new FakeAppendFileFile)));
    }

    function testDeleteReturnsTrue()
    {
        $append = $this->createAppendFile();
        $id = $append->addFile(new FakeAppendFileFile);
        $this->assertTrue($append->delete($id));
    }

    function testUnDeleteReturnsTrue()
    {
        $append = $this->createAppendFile();
        $id = $append->addFile(new FakeAppendFileFile);
        $append->delete($id);
        $this->assertTrue($append->undelete(1));
    }


}
?>