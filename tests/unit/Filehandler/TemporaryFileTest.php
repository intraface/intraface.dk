<?php
require_once 'Intraface/modules/filemanager/TemporaryFile.php';
require_once 'file_functions.php';

class FakeTemporaryFileFileHandler {

    public $upload_path;
    public $tempdir_path;

    function __construct() {
        $this->upload_path = PATH_UPLOAD.'1'. DIRECTORY_SEPARATOR;
        $this->tempdir_path = $this->upload_path.PATH_UPLOAD_TEMPORARY;
    }

}

class TemporaryFileTest extends PHPUnit_Framework_TestCase
{

    function createFileHandler()
    {
        $kernel = new Stub_Kernel;
        return new FakeTemporaryFileFileHandler($kernel);
    }


    function setUp() {

    }

    //////////////////////////////////////////////////

    function testConstruct()
    {
        $tf = new TemporaryFile($this->createFileHandler());
        $this->assertEquals('TemporaryFile', get_class($tf));
    }

    function testConstructWithFileNameWithSpacesAndSlashes()
    {
        $tf = new TemporaryFile($this->createFileHandler(), 'this is a very\ wrong name/.jpg');
        $this->assertEquals('this_is_a_very__wrong_name_.jpg', $tf->getFileName());
    }

    function testConstructWithTooLongFileName()
    {
        $tf = new TemporaryFile($this->createFileHandler(), '123456789012345678901234567890123456789012345678901234567890.jpg');
        $this->assertEquals('1234567890123456789012345678901234567890123456.jpg', $tf->getFileName());
    }

    function testGetFilePath()
    {
        $tf = new TemporaryFile($this->createFileHandler(), 'file_name.jpg');

        $this->assertEquals(PATH_UPLOAD.'1'.DIRECTORY_SEPARATOR.PATH_UPLOAD_TEMPORARY, substr($tf->getFilePath(), 0, strlen(PATH_UPLOAD) + 1 + strlen(DIRECTORY_SEPARATOR) + strlen(PATH_UPLOAD_TEMPORARY)));
        $this->assertEquals(strlen(PATH_UPLOAD) + 1 + strlen(DIRECTORY_SEPARATOR) + strlen(PATH_UPLOAD_TEMPORARY) + 13 + strlen(DIRECTORY_SEPARATOR) + strlen('file_name.jpg'), strlen($tf->getFilePath()));
        $this->assertEquals('file_name.jpg', substr($tf->getFilePath(), -strlen('file_name.jpg')));

        // ereg('^'.PATH_UPLOAD.'1(/|\\\\)'.PATH_UPLOAD_TEMPORARY.'([a-zA-Z0-9]{13})(/|\\\\)file_name.jpg$', str_replace('\\', '\\\\', $tf->getFilePath()), $regs);
        // $this->assertEquals(PATH_UPLOAD.'1'.$regs[1].PATH_UPLOAD_TEMPORARY.$regs[2].$regs[3].'file_name.jpg', $tf->getFilePath());
    }

    function testGetFileDir()
    {
        $tf = new TemporaryFile($this->createFileHandler(), 'file_name.jpg');

        $this->assertEquals(PATH_UPLOAD.'1'.DIRECTORY_SEPARATOR.PATH_UPLOAD_TEMPORARY, substr($tf->getFilePath(), 0, strlen(PATH_UPLOAD) + 1 + strlen(DIRECTORY_SEPARATOR) + strlen(PATH_UPLOAD_TEMPORARY)));
        $this->assertEquals(strlen(PATH_UPLOAD) + 1 + strlen(DIRECTORY_SEPARATOR) + strlen(PATH_UPLOAD_TEMPORARY) + 13 + strlen(DIRECTORY_SEPARATOR), strlen($tf->getFileDir()));

        // ereg('^'.PATH_UPLOAD.'1(/|\\\\)'.PATH_UPLOAD_TEMPORARY.'([a-zA-Z0-9]{13})(/|\\\\)$', str_replace('\\', '\\\\', $tf->getFilePath()), $regs);
        // $this->assertEquals(PATH_UPLOAD.'1'.$regs[1].PATH_UPLOAD_TEMPORARY.$regs[2].$regs[3], $tf->getFileDir());
    }

    function testGetFilePathIsUnique()
    {
        $tf = new TemporaryFile($this->createFileHandler(), 'file_name.jpg');
        $file_path1 = $tf->getFilePath();

        $tf->setFileName('file_name.jpg');
        $file_path2 = $tf->getFilePath();

        $this->assertNotEquals($file_path1, $file_path2);
    }

}
