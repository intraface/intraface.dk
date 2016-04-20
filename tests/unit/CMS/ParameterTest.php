<?php
require_once 'CMSStubs.php';
require_once 'Intraface/Kernel.php';
require_once 'Intraface/modules/cms/Parameter.php';

class FakeObjectToPutIntoParameter
{
    public $kernel;

    function __construct()
    {
        $this->kernel = new Stub_Kernel;
    }

    function get($type)
    {
        switch ($type) {
            case 'identify_as':
                return 'cms_element';
                break;
            default:
                return 1;
                exit;
        }

    }
}

class ParameterTest extends PHPUnit_Framework_TestCase
{
    protected $db;

    function setUp()
    {
        $this->db = MDB2::singleton(DB_DSN);
        $this->parameter = $this->createParameter();
    }

    function tearDown()
    {
        $this->db->exec('TRUNCATE cms_parameter');
    }

    function createParameter()
    {
        return new CMS_Parameter(new FakeObjectToPutIntoParameter);
    }

    function testConstruction()
    {
        $this->assertTrue(is_object($this->parameter));
    }

    function testSaveSavesValuesAndTheyCanBeGottenRightAway()
    {
        $parameter = 'parameter';
        $value = 'value';
        $this->assertTrue($this->parameter->save($parameter, $value));
        $this->assertEquals($value, $this->parameter->get($parameter));
    }

    function testSaveActuallyPersistsValuesForLaterUsage()
    {
        $parameter = 'parameter';
        $value = 'value';
        $this->assertTrue($this->parameter->save($parameter, $value));
        $this->assertEquals($value, $this->parameter->get($parameter));

        $p = $this->createParameter();
        $this->assertEquals($value, $p->get($parameter));
    }
}
