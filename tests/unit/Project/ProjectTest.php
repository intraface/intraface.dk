<?php
require_once dirname(__FILE__) . '/../config.test.php';
require_once 'Intraface/modules/project/Project.php';
require_once 'MDB2.php';

class ProjectTest extends PHPUnit_Framework_TestCase
{
    private $project;

    function setUp()
    {
        $this->project = new Intraface_Project(MDB2::singleton(DB_DSN), new Stub_User);
    }

    function testConstruction()
    {
        $this->assertTrue(is_object($this->project));
    }

    function testSaveReturnsIntegerAndNowTheCorrectValuesCanBeRetrieved()
    {
        $name = 'Project';
        $description = 'My description';
        $data = array('name' => $name, 'description' => $description);
        $id = $this->project->save($data);
        $this->assertTrue($id > 0);
        $this->assertEquals($name, $this->project->getName());
        $this->assertEquals($description, $this->project->getDescription());

    }

}