<?php
require_once dirname(__FILE__) . '/../config.test.php';

require_once 'PHPUnit/Framework.php';
require_once 'Intraface/Standard.php';
require_once 'Intraface/functions/functions.php';
require_once 'Intraface/modules/filemanager/FileManager.php';
require_once 'Intraface/modules/filemanager/ImageRandomizer.php';
require_once 'Intraface/shared/keyword/Keyword.php';

class FakeImageRandomizerKernel {
    public $intranet;
    public $user;

    function randomKey() {
        return 'thisisnotreallyarandomkey'.microtime();
    }
}


class FakeImageRandomizerIntranet
{
    function get()
    {
        return 1;
    }
}

class FakeImageRandomizerUser
{
    function get()
    {
        return 1;
    }
}

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

class ImageRandomizerTest extends PHPUnit_Framework_TestCase
{

    private $file_name = 'wideonball.jpg';

    function setUp()
    {
        $db = MDB2::factory(DB_DSN);
        $db->query('TRUNCATE file_handler');
        $db->query('TRUNCATE file_handler_instance');
        $db->query('TRUNCATE keyword');
        $db->query('TRUNCATE keyword_x_object');
        iht_deltree(PATH_UPLOAD.'1');
        if(file_exists(PATH_UPLOAD.'/1/1.jpeg')) {
            unlink(PATH_UPLOAD.'/1/1.jpeg');
        }

    }

    function createKernel()
    {
        $kernel = new FakeImageRandomizerKernel;
        $kernel->intranet = new FakeImageRandomizerIntranet;
        $kernel->user = new FakeImageRandomizerUser;
        return $kernel;
    }

    function createImageRandomizer($keyword = array('test'))
    {
        
        return new ImageRandomizer(new FileManager($this->createKernel()), $keyword);
    }
    
    function createImages() {
        for($i = 1; $i < 11; $i++) {
            $filemanager = new FileManager($this->createKernel());
            copy(dirname(__FILE__) . '/'.$this->file_name, PATH_UPLOAD.$this->file_name);
            $filemanager->save(PATH_UPLOAD.$this->file_name, 'file'.$i.'.jpg');
            $appender = $filemanager->getKeywordAppender();
            
            $string_appender = new Intraface_Keyword_StringAppender(new Keyword($filemanager), $appender);
            if(round($i/2) == $i/2) {
                $t = 'A';
            }
            else {
                $t = 'B';
            }
            $string_appender->addKeywordsByString('test, test_'.$t);
        }
    }

    
    ////////////////////////////////////////////////////////////////

    function testImageRandomizerConstructor() {
        $this->createImages();
        $r = $this->createImageRandomizer();

        $this->assertEquals('ImageRandomizer', get_class($r));
    }
    
    function testGetRandomImageReturnsFileHandlerObject() {
        $this->createImages();
        $r = $this->createImageRandomizer();
        $file = $r->getRandomImage();
        $this->assertTrue(is_object($file));
        $this->assertEquals('FileHandler', get_class($file));
    }
    
    function testGetRandomImageReturnsFileHandlerObjectWithCorrectFileName() {
        $this->createImages();
        $r = $this->createImageRandomizer();
        $file = $r->getRandomImage();
        $this->assertTrue(is_object($file));
        $this->assertEquals(1, ereg("^file[0-9]{1,2}\.jpg$", $file->get('file_name')), 'file_name "'.$file->get('file_name').'" not valid');
    }
    
    function testGetRandomImageReturnsDifferentImages() {
        $this->createImages();
        $r = $this->createImageRandomizer();
        $file1 = $r->getRandomImage();
        $file2 = $r->getRandomImage();
        $this->assertNotEquals($file1->get('file_name'), $file2->get('file_name'));
    }
    
    function testGetRandomImageDoesNotTriggerErrorOnDeletedKeyword() {
        // first we add and delete a keyword used later
        $filemanager = new FileManager($this->createKernel()); 
        $keyword = new Keyword($filemanager);
        $keyword->save(array('keyword' => 'test_A'));
        $keyword->delete();
        
        $this->createImages();
        $r = $this->createImageRandomizer(array('test', 'test_A'));
        $file = $r->getRandomImage();
        $this->assertEquals('FileHandler', get_class($file));
                
    }

}
?>
