<?php
require_once dirname(__FILE__) . '/../config.test.php';

require_once 'PHPUnit/Framework.php';
require_once 'Intraface/Standard.php';
require_once 'Intraface/functions/functions.php';
require_once 'Intraface/shared/filehandler/FileHandler.php';

class FakeInstanceHandlerKernel {
    public $intranet;
    public $user;

    function randomKey() {
        return 'thisisnotreallyarandomkey'.microtime();
    }
}


class FakeInstanceHandlerIntranet
{
    function get()
    {
        return 1;
    }
}

class FakeInstanceHandlerUser
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

class InstanceHandlerTest extends PHPUnit_Framework_TestCase
{

    private $file_name = 'wideonball.jpg';

    function setUp()
    {
        $db = MDB2::factory(DB_DSN);
        $db->query('TRUNCATE file_handler');
        $db->query('TRUNCATE file_handler_instance');
        iht_deltree(PATH_UPLOAD.'1');
        if(file_exists(PATH_UPLOAD.'/1/1.jpeg')) {
            unlink(PATH_UPLOAD.'/1/1.jpeg');
        }

    }

    function createKernel()
    {
        $kernel = new FakeInstanceHandlerKernel;
        $kernel->intranet = new FakeInstanceHandlerIntranet;
        $kernel->user = new FakeInstanceHandlerUser;
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
        copy(dirname(__FILE__) . '/'.$this->file_name, PATH_UPLOAD.$this->file_name);
        $filehandler->save(PATH_UPLOAD.$this->file_name, $this->file_name);
        $filehandler->load();
        $this->assertEquals('', $filehandler->error->view());
        return $filehandler;
    }
    ////////////////////////////////////////////////////////////////

    function testCreateFile() {
        $file = $this->createFile();

        $this->assertEquals($this->file_name, $file->get('file_name'));
    }


    function testConstructWithoutParameters() {

        $filehandler = $this->createFile();
        $filehandler->createInstance();
        $this->assertTrue(is_object($filehandler->instance));

    }

    function testConstructWithTypeSquare() {
        $filehandler = $this->createFile();
        $filehandler->createInstance('square');

        $this->assertEquals(3844, $filehandler->instance->get('file_size'));
    }

    function testConstructWithTypeSquareAndCropParams() {
        $filehandler = $this->createFile();

        $crop = array('crop_offset_x' => 200,
            'crop_offset_y' => 20,
            'crop_width' => 100,
            'crop_height' => 100);

        $filehandler->createInstance('square', $crop);
        
        $this->assertEquals(3644, filesize($filehandler->instance->get('file_path')));
        $size = getimagesize($filehandler->instance->get('file_path')); 
        $this->assertEquals(75, $size[0]);
        $this->assertEquals(75, $size[1]);
        
        // $this->assertEquals('c6fc157c4d2d56ad8be50a71af684fab', md5(file_get_contents($filehandler->instance->get('file_path'))));

        // instance handler does not return the crop parameter, so we just find it in the database!
        $db = MDB2::factory(DB_DSN);
        $result = $db->query('SELECT crop_parameter FROM file_handler_instance WHERE id = '.$filehandler->instance->get('id'));
        $row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
        $this->assertEquals($crop, unserialize($row['crop_parameter']));

    }


}
?>
