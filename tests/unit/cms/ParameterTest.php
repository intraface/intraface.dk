<?php
require_once dirname(__FILE__) . './../config.test.php';

require_once 'PHPUnit/Framework.php';
require_once 'CMSStubs.php';
require_once 'Intraface/Kernel.php';
require_once 'Intraface/modules/cms/Parameter.php';

class FakeObjectToPutIntoParameter
{
    public $kernel;

    function __construct()
    {
        $this->kernel = new FakeParameterKernel;
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

class FakeParameterIntranet
{
    function get()
    {
        return 1;
    }
}

class FakeParameterKernel
{
    public $intranet;
    function __construct()
    {
        $this->intranet = new FakeParameterIntranet;
    }
}

class ParameterTest extends PHPUnit_Framework_TestCase
{

    function setUp()
    {
        $this->parameter = $this->createParameter();
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
?>