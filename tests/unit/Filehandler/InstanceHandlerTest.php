<?php
require_once 'Intraface/functions.php';
require_once 'Intraface/modules/filemanager/FileHandler.php';
require_once 'Intraface/modules/filemanager/InstanceManager.php';
require_once 'file_functions.php';

class InstanceHandlerTest extends PHPUnit_Framework_TestCase
{
    function setUp()
    {
        $db = MDB2::singleton(DB_DSN);
        $db->query('TRUNCATE file_handler');
        $db->query('TRUNCATE file_handler_instance');
        $db->query('TRUNCATE file_handler_instance_type');
        iht_deltree(PATH_UPLOAD.'1');
        if (file_exists(PATH_UPLOAD.'/1/1.jpeg')) {
            unlink(PATH_UPLOAD.'/1/1.jpeg');
        }
    }

    function createKernel()
    {
        $kernel = new Stub_Kernel;
        return $kernel;
    }

    function createFileHandler()
    {
        return new FileHandler($this->createKernel());
    }

    function createFile($file)
    {
        $data = array('file_name' => $file);
        $filehandler = $this->createFileHandler();
        copy(dirname(__FILE__) . '/'.$file, PATH_UPLOAD.$file);
        $filehandler->save(PATH_UPLOAD.$file, $file);
        $filehandler->load();
        $this->assertEquals('', $filehandler->error->view());
        return $filehandler;
    }
    ////////////////////////////////////////////////////////////////

    function testCreateFile()
    {
        $file = $this->createFile('wideonball.jpg');

        $this->assertEquals('wideonball.jpg', $file->get('file_name'));
    }

    function testConstructWithoutParameters()
    {
        $filehandler = $this->createFile('wideonball.jpg');
        $filehandler->createInstance();
        $this->assertTrue(is_object($filehandler->instance));
    }

    function testConstructWithTypeSquare()
    {
        $filehandler = $this->createFile('wideonball.jpg');
        $filehandler->createInstance('square');

        $this->assertEquals(3846, $filehandler->instance->get('file_size'), '', 10);
    }

    function testConstructWithTypeSquareAndCropParams()
    {
        $filehandler = $this->createFile('wideonball.jpg');

        $crop = array('crop_offset_x' => 200,
            'crop_offset_y' => 20,
            'crop_width' => 100,
            'crop_height' => 100);

        $filehandler->createInstance('square', $crop);
        // we add 10 bytes delta
        $this->assertEquals(3644, filesize($filehandler->instance->get('file_path')), '', 10);
        $size = getimagesize($filehandler->instance->get('file_path'));
        $this->assertEquals(75, $size[0]);
        $this->assertEquals(75, $size[1]);

        // $this->assertEquals('c6fc157c4d2d56ad8be50a71af684fab', md5(file_get_contents($filehandler->instance->get('file_path'))));

        // instance handler does not return the crop parameter, so we just find it in the database!
        $db = MDB2::singleton(DB_DSN);
        $result = $db->query('SELECT crop_parameter FROM file_handler_instance WHERE id = '.$filehandler->instance->get('id'));
        $row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
        $this->assertEquals($crop, unserialize($row['crop_parameter']));

    }

    function testCreateCustomInstanceCreaterThanImage()
    {
        $im = new InstanceManager($this->createKernel());

        $this->assertEquals(1000, $im->save(array('name' => 'wide', 'max_height' => 280, 'max_width' => 720, 'resize_type' => 'strict')));

        $filehandler = $this->createFile('idraetshoejskolen9.jpg');
        $filehandler->createInstance('wide');
        // we add 20 bytes delta
        $this->assertEquals(54510, filesize($filehandler->instance->get('file_path')), '', 20);
        $size = getimagesize($filehandler->instance->get('file_path'));
        $this->assertEquals(720, $size[0]);
        $this->assertEquals(280, $size[1]);
    }
}
