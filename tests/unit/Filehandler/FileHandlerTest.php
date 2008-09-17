<?php
require_once dirname(__FILE__) . '/../config.test.php';

require_once 'Intraface/functions.php';
require_once 'Intraface/shared/filehandler/FileHandler.php';

class FakeFileHandlerKernel {
    public $intranet;
    public $user;
    /*
    function randomKey() {
        return 'thisisnotreallyarandomkey'.microtime();
    }*/
}


class FakeFileHandlerIntranet
{
    function get()
    {
        return 1;
    }
}

class FakeFileHandlerUser
{
    function get()
    {
        return 1;
    }
}

function fht_deltree( $f ){

    if( is_dir( $f ) ){
        foreach( scandir( $f ) as $item ){
            if( !strcmp( $item, '.' ) || !strcmp( $item, '..' ) )
                continue;
            fht_deltree( $f . "/" . $item );
        }
        rmdir( $f );
    }
    else{
        @unlink( $f );
    }
}


class FileHandlerTest extends PHPUnit_Framework_TestCase
{
    private $file_name = 'tester.jpg';

    function setUp() {
        $db = MDB2::factory(DB_DSN);
        $db->query('TRUNCATE file_handler');
        fht_deltree(PATH_UPLOAD . '1');
    }

    function createKernel()
    {
        $kernel = new FakeFileHandlerKernel;
        $kernel->intranet = new FakeFileHandlerIntranet;
        $kernel->user = new FakeFileHandlerUser;
        return $kernel;
    }

    function createFileHandler()
    {
        return new FileHandler($this->createKernel());
    }

    function createFile()
    {
        $data = array('file_name' => $this->file_name);
        $filehandler = $this->createFileHandler();
        $this->assertTrue($filehandler->update($data) > 0);
        return $filehandler;
    }

    ////////////////////////////////////////////////////////////////

    function testConstruction()
    {
        $filehandler = $this->createFileHandler();
        $this->assertTrue(is_object($filehandler));
    }

    function testFactoryReturnsAValidFileHandlerObject()
    {
        $fh = $this->createFile();
        $accesskey = $fh->getAccessKey();
        $filehandler = FileHandler::factory($this->createKernel(), $accesskey);
        $this->assertTrue(is_object($filehandler));
    }

    function testUpdate()
    {
        $fh = $this->createFile();
        $this->assertEquals($this->file_name, $fh->get('file_name'));
    }

    function testDelete()
    {
        // @todo how do we test precisely that it is deleted
        $fh = $this->createFile();
        $id = $fh->getId();

        $fh = new FileHandler($this->createKernel(), $id);
        $this->assertTrue($fh->delete());
    }

    function testUnDelete()
    {
        // @todo how do we test precisely that it is undeleted
        $fh = $this->createFile();
        $fh->delete();
        $this->assertTrue($fh->undelete());

    }

    function testSave()
    {
        $fh = new FileHandler($this->createKernel());
        // first we make a copy of the file as it is moved by upload.
        copy(dirname(__FILE__) . '/wideonball.jpg', PATH_UPLOAD.'wideonball.jpg');
        $id = $fh->save(PATH_UPLOAD.'wideonball.jpg', 'Filename');
        $fh->error->view();
        $this->assertTrue($id > 0);
    }
    
    function testAccessKeyIsValid()
    {
        $fh = new FileHandler($this->createKernel());
        // first we make a copy of the file as it is moved by upload.
        copy(dirname(__FILE__) . '/wideonball.jpg', PATH_UPLOAD.'wideonball.jpg');
        $id = $fh->save(PATH_UPLOAD.'wideonball.jpg', 'Filename');
        $fh->load();
        $this->assertEquals(50, strlen($fh->get('access_key')));
    }

    function testCreateTemporaryFile() {
        $fh = new FileHandler($this->createKernel());
        $this->assertEquals('TemporaryFile', get_class($fh->createTemporaryFile()));
    }
}
?>