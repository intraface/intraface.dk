<?php

require_once dirname(__FILE__) . '/../config.test.php';
require_once 'PHPUnit/Framework.php';

require_once 'Intraface/modules/project/Task.php';
require_once 'MDB2.php';

error_reporting(E_ALL);

class FakeTaskProject
{
    function getId()
    {
        return 1;
    }

    function getUser()
    {
        return new Stub_User;
    }
}

class TaskTest extends PHPUnit_Framework_TestCase
{
    private $task;

    function setUp()
    {
        $this->task = new Intraface_Project_Task(MDB2::singleton(DB_DSN), new FakeTaskProject);
    }

    function testConstruction()
    {
        $this->assertTrue(is_object($this->task));
    }

    function testSaveReturnsIntegerAndNowTheCorrectValuesCanBeRetrieved()
    {
        $name = 'Project';
        $data = array('item' => $name);
        $id = $this->task->save($data);
        $this->assertTrue($id > 0);
        $this->assertEquals($name, $this->task->getItem());

    }

}