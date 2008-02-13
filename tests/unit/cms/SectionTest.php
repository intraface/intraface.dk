<?php
require_once dirname(__FILE__) . './../config.test.php';

require_once 'PHPUnit/Framework.php';

require_once 'CMSStubs.php';
require_once 'Intraface/Kernel.php';
require_once 'Intraface/modules/cms/Section.php';

class SectionTest extends PHPUnit_Framework_TestCase
{

    private $kernel;
    private $page;

    function setUp()
    {
        $this->kernel = new Kernel;
        $this->kernel->user = new FakeCMSUser;
        $this->kernel->intranet = new FakeCMSIntranet;
        $this->kernel->setting = new FakeCMSSetting;
        $this->kernel->module('cms');
        $this->site = new FakeCMSSite($this->kernel);
        $this->page = new FakeCMSPage($this->site);

    }

    function testConstruction()
    {
        $section = new CMS_Section($this->page);
        $this->assertTrue(is_object($section));
    }

    function testFactory()
    {
        $section = CMS_Section::factory($this->page, 'type', 'shorttext');
        $this->assertTrue(is_object($section));
    }

    function testAddParameter()
    {
        $section = CMS_Section::factory($this->page, 'type', 'shorttext');
        // a bit of cheating here :)
        $section->value['id'] = 1;
        $this->assertTrue($section->addParameter('test', 'test'));
    }

    function testSave()
    {
        $site = new CMS_Section($this->page);
        $site_array = array(
            'type_key' => 1,
            'template_section_id' => 1
        );
        $this->assertTrue($site->save($site_array) > 0);
    }

}