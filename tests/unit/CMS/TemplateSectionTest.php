<?php
require_once 'CMSStubs.php';
require_once 'Intraface/Kernel.php';
require_once 'Intraface/modules/cms/TemplateSection.php';

class TemplateSectionTest extends PHPUnit_Framework_TestCase
{

    function setUp()
    {
        $this->kernel = $this->createKernel();

    }

    function createKernel()
    {
        $this->kernel = new Stub_Intranet;

        return $this->kernel;
    }

    function createTemplate()
    {
        return new FakeCMSTemplate();
    }

    function createSite()
    {
        return new FakeCMSSite();
    }

    function testConstruction()
    {
        $site = new CMS_TemplateSection($this->createTemplate());
        $this->assertTrue(is_object($site));
    }

    function testSaveReturnsInteger()
    {
        $section = new CMS_TemplateSection($this->createTemplate());
        $section->value['type_key'] = 1;
        $data = array('identifier' => uniqid(), 'name' => 'name');
        $this->assertTrue($section->save($data) > 0);
    }

    function testAddParameterReturnsTrue()
    {
        $section = new CMS_TemplateSection($this->createTemplate());
        $section->value['type_key'] = 1;
        $data = array('identifier' => uniqid(), 'name' => 'name');
        $this->assertTrue($section->save($data) > 0);
        $this->assertTrue($section->addParameter('test', 'test'));
    }

    function testDelete()
    {
        $section = new CMS_TemplateSection($this->createTemplate());
        $section->value['type_key'] = 1;
        $data = array('identifier' => uniqid(), 'name' => 'name');
        $this->assertTrue($section->delete($data));
    }
}
