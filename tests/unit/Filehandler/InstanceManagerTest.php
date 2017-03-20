<?php
require_once 'Intraface/Standard.php';
require_once 'Intraface/Error.php';
require_once 'Intraface/modules/filemanager/FileHandler.php';
require_once 'Intraface/modules/filemanager/InstanceManager.php';
require_once 'file_functions.php';

class InstanceManagerTest extends PHPUnit_Framework_TestCase
{
    function setUp()
    {
        $db = MDB2::singleton(DB_DSN);
        $db->query('TRUNCATE file_handler_instance_type');
    }

    function createInstanceManager($id = 0)
    {
        return new InstanceManager(new Stub_Kernel, $id);
    }

    function testConstruction()
    {
        $im = $this->createInstanceManager();
        $this->assertTrue(is_object($im));
    }

    function testConstructionOnStandardType()
    {
        $im = $this->createInstanceManager(1);
        $this->assertEquals('square', $im->get('name'));
    }

    function testConstructionOnInvalidType()
    {
        $im = $this->createInstanceManager(999999);
        $this->assertEquals(0, $im->get('type_key'));
    }

    function testSaveWithEmptyArray()
    {
        $im = $this->createInstanceManager();
        $this->assertFalse($im->save(array()));
    }

    function testSaveWithOverwriteStandardType()
    {
        $im = $this->createInstanceManager(2);
        $input = array('name' => 'some-new-name-which-will-not-be-used',
            'max_width' => 100,
            'max_height' => 100,
            'resize_type' => 'strict');

        $this->assertEquals(2, $im->save($input));
    }

    function testLoadAfterOverwriteStandardType()
    {
        $im = $this->createInstanceManager(2);

        $input = array('name' => 'some-new-name-which-will-not-be-used',
            'max_width' => 101,
            'max_height' => 101,
            'resize_type' => 'strict');

        $im->save($input);

        $im = $this->createInstanceManager(2);
        $output = array(
            'type_key' => 2,
            'name' => 'thumbnail',
            'fixed' => false,
            'hidden' => false,
            'max_width' => 101,
            'max_height' => 101,
            'resize_type' => 'strict',
            'resize_type_key' => 1,
            'origin' => 'overwritten'
        );

        $this->assertEquals($output, $im->get());
    }

    function testSaveNewType()
    {
        $im = $this->createInstanceManager();
        $input = array('name' => 'new-type',
            'max_width' => 240,
            'max_height' => 130,
            'resize_type' => 'relative');

        $this->assertEquals(1000, $im->save($input));
    }

    function testLoadAfterSaveNewType()
    {
        $im = $this->createInstanceManager();

        $input = array('name' => 'new-type',
            'max_width' => 240,
            'max_height' => 130,
            'resize_type' => 'relative');

        $im->save($input);

        $im = $this->createInstanceManager(1000);
        $output = array(
            'name' => 'new-type',
            'type_key' => 1000,
            'max_width' => 240,
            'max_height' => 130,
            'resize_type_key' => 0,
            'resize_type' => 'relative',
            'origin' => 'custom'
        );

        $this->assertEquals($output, $im->get());
    }

    function testGetList()
    {
        $im = $this->createInstanceManager();

        $input = array('name' => 'new-type',
            'max_width' => 240,
            'max_height' => 130,
            'resize_type' => 'relative');

        $im->save($input);

        $this->assertEquals(7, count($im->getList()));
    }
}
