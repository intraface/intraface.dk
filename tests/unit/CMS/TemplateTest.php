<?php
require_once dirname(__FILE__) . './../config.test.php';

require_once 'PHPUnit/Framework.php';
require_once 'CMSStubs.php';
require_once 'Intraface/Kernel.php';
require_once 'Intraface/modules/cms/Template.php';

class TemplateTest extends PHPUnit_Framework_TestCase
{

    function setUp()
    {
        $this->kernel = $this->createKernel();
    }

    function createKernel()
    {
        $this->kernel = new Stub_Kernel;

        return $this->kernel;
    }

    function createTemplate()
    {
        return new FakeCMSTemplate();
    }

    function createSite()
    {
        return new FakeCMSSite($this->kernel);
    }

    function testConstruction()
    {
        $site = new CMS_Template($this->createSite());
        $this->assertTrue(is_object($site));
    }

    function testDelete()
    {
        $site = new CMS_Template($this->createSite());
        $this->assertTrue($site->delete());
    }

}
