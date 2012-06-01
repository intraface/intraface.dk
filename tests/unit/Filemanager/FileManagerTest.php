<?php
require_once 'Intraface/Standard.php';
require_once 'Intraface/Kernel.php';
require_once 'Intraface/modules/filemanager/FileManager.php';
require_once 'DB/Sql.php';

class FileManagerTest extends PHPUnit_Framework_TestCase
{
    private $file_name = 'tester.jpg';

    function createKernel()
    {
        $kernel = new Stub_Kernel;
        return $kernel;
    }

    function createFileManager()
    {
        return new Intraface_modules_filemanager_FileManager($this->createKernel());
    }

    function createFile()
    {
        $data = array('file_name' => $this->file_name);
        $filemanager = $this->createFileManager();
        $this->assertTrue($filemanager->update($data) > 0);
        return $filemanager;
    }

    ////////////////////////////////////////////////////////////////

    function testConstruction()
    {
        $filemanager = $this->createFileManager();
        $this->assertTrue(is_object($filemanager));
    }

    function testCreateDBQuery() {
        $filemanager = $this->createFileManager();
        $filemanager->getDBQuery();
        $this->assertTrue(is_object($filemanager->dbquery));
    }
}
?>
