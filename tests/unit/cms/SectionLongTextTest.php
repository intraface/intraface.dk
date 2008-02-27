<?php
require_once dirname(__FILE__) . './../config.test.php';

require_once 'PHPUnit/Framework.php';

require_once 'CMSStubs.php';
require_once 'Intraface/Kernel.php';
require_once 'Intraface/modules/cms/Section.php';

define('PATH_CACHE', './');

class Testable_CMS_Section_LongText extends CMS_Section_LongText
{
    function getTemplateSection()
    {
        return new FakeCMSTemplateSection($this->kernel);
    }
}

class SectionLongTextTest extends PHPUnit_Framework_TestCase {

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
        $section = new CMS_Section_LongText($this->page);
        $this->assertTrue(is_object($section));
    }

    function testSaveReturnsTrue()
    {

        $section = new Testable_CMS_Section_LongText($this->page);
        $section->getParameter();
        $data = array('type_key' => 1, 'template_section_id' => 1);
        $section->save($data);
        $section->template_section = new FakeCMSTemplateSection($this->kernel);
        $data = array('text' => 'Some text');
        $section->save_section($data);
    }
}