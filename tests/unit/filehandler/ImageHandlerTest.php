<?php
require_once dirname(__FILE__) . '/../config.test.php';

require_once 'PHPUnit/Framework.php';
require_once 'Intraface/Standard.php';
require_once 'Intraface/functions/functions.php';
require_once 'Intraface/shared/filehandler/FileHandler.php';
require_once 'Intraface/shared/filehandler/ImageHandler.php';

class FakeImageHandlerKernel {
    public $intranet;
    public $user;

}


class FakeImageHandlerIntranet
{
    function get()
    {
        return 1;
    }
}

class FakeImageHandlerUser
{
    function get()
    {
        return 1;
    }
}

if(!function_exists('iht_deltree')) {
    
    function iht_deltree( $f ){
    
        if( is_dir( $f ) ){
            foreach( scandir( $f ) as $item ){
                if( !strcmp( $item, '.' ) || !strcmp( $item, '..' ) )
                    continue;
                iht_deltree( $f . "/" . $item );
            }
            rmdir( $f );
        }
        else{
            @unlink( $f );
        }
    }
}

class ImageHandlerTest extends PHPUnit_Framework_TestCase
{

    private $file_name = 'wideonball.jpg';

    function setUp()
    {
        $db = MDB2::factory(DB_DSN);
        $db->query('TRUNCATE file_handler');
        iht_deltree(PATH_UPLOAD.'1');
        if(file_exists(PATH_UPLOAD.'/1/1.jpeg')) {
            unlink(PATH_UPLOAD.'/1/1.jpeg');
        }

    }

    function createKernel()
    {
        $kernel = new FakeImageHandlerKernel;
        $kernel->intranet = new FakeImageHandlerIntranet;
        $kernel->user = new FakeImageHandlerUser;
        return $kernel;
    }

    function createFileHandler()
    {
        
        $data = array('file_name' => $this->file_name);
        $filehandler = new FileHandler($this->createKernel());
        copy(dirname(__FILE__) . '/'.$this->file_name, PATH_UPLOAD.$this->file_name);
        $filehandler->save(PATH_UPLOAD.$this->file_name, $this->file_name);
        $filehandler->load();
        $this->assertEquals('', $filehandler->error->view());
        return $filehandler;
    }

    ////////////////////////////////////////////////////////////////

    function testConstruct() {
        $image = new ImageHandler($this->createFileHandler());
        $this->assertEquals('ImageHandler', get_class($image));
    }


    function testResizeWithRelativeSize() {

        $image = new ImageHandler($this->createFileHandler());
        $file = $image->resize(200, 600);
        $size = getimagesize($file);
        $this->assertEquals(200, $size[0]);
        $this->assertEquals(50, $size[1]);
    }
    
    function testResizeWithStrictSize() {

        $image = new ImageHandler($this->createFileHandler());
        $file = $image->resize(200, 300, 'strict');
        $size = getimagesize($file);
        $this->assertEquals(200, $size[0]);
        $this->assertEquals(300, $size[1]);
    }

    function testCrop() {
        $image = new ImageHandler($this->createFileHandler());
        $file = $image->crop(100, 100, 200, 20);
        $size = getimagesize($file);
        $this->assertEquals(100, $size[0]);
        $this->assertEquals(100, $size[1]);

    }
    
    function testQualityAfterRepeatedResize() {
        
        $image = new ImageHandler($this->createFileHandler());
        $image->resize(500, 200);
        $image->resize(300, 200);
        $file1 = $image->resize(200, 200);
        
        $image = new ImageHandler($this->createFileHandler());
        $file2 = $image->resize(200, 200);
        
        // we accept 10% fall in quality! after several resize
        $this->assertEquals(filesize($file2), filesize($file1), '', filesize($file2)/100*10);
        
    }


}
?>
