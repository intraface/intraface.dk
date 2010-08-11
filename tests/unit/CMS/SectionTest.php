<?php
require_once dirname(__FILE__) . './../config.test.php';

require_once 'PHPUnit/Framework.php';

require_once 'CMSStubs.php';
require_once 'Intraface/Kernel.php';
require_once 'Intraface/modules/cms/Section.php';
require_once 'Intraface/modules/cms/TemplateSection.php';

class FakeThisSectionTemplate
{
    public $kernel;
    public $cmssite;

    function __construct($kernel)
    {
        $this->kernel = $kernel;
    }

    function get()
    {
        return 1;
    }
}

class Testable_CMS_Section extends CMS_Section {

    protected function getTemplateSection($template_section_id)
    {
        return new FakeCMSTemplateSection(NULL);
        // return CMS_TemplateSection::factory($this->kernel, 'id', $template_section_id);
    }

}

class SectionTest extends PHPUnit_Framework_TestCase
{

    private $kernel;
    private $page;

    function setUp()
    {
        $this->kernel = new Stub_Kernel;
        $this->site = new FakeCMSSite($this->kernel);
        $this->page = new FakeCMSPage($this->site);

    }

    function saveTemplateSection()
    {
        $t = new FakeThisSectionTemplate($this->kernel);
        $t->cmssite = $this->site;
        $template = new CMS_TemplateSection($t);
        $template->value['type_key'] = 1;
        $data = array('identifier' => uniqid(), 'name' => 'test', 'template_id' => $this->saveTemplate());
        $template->save($data);
        return $template->getId();
    }

    function saveTemplate()
    {
        $t = new CMS_Template($this->site);
        $data = array('name' => 'test', 'identifier' => 'name');
        $t->save($data);
        return $t->getId();
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
        $section = new Testable_CMS_Section($this->page);
        $section_array = array(
            'type_key' => 1,
            'template_section_id' => $this->saveTemplateSection()
        );
        $this->assertTrue($section->save($section_array) > 0);
    }

}