<?php
require_once dirname(__FILE__) . './../config.test.php';

require_once 'PHPUnit/Framework.php';

require_once 'CMSStubs.php';
require_once 'Intraface/Kernel.php';

class SectionTest extends PHPUnit_Framework_TestCase {

    private $kernel;
    private $page;
    /*
    function setUp() {
        $this->kernel = new Kernel;
        $this->kernel->user = new FakeUser;
        $this->kernel->intranet = new FakeIntranet;
        $this->kernel->setting = new FakeSetting;
        $this->kernel->module('cms');
        $this->site = new FakeSite($this->kernel);
        $this->page = new FakePage($this->site);

    }

    */
    function testConstruction() {
        $this->markTestIncomplete('not completed');
    }

    /*
    function testConstruction() {
        $section = new CMS_Section($this->page);
        $this->assertTrue(is_object($section));
    }

    function testFactory() {
        $section = CMS_Section::factory($this->page, 'type', 'shorttext');
        $this->assertTrue(is_object($section));
    }

    function testAddParameter() {
        $section = CMS_Section::factory($this->page, 'type', 'shorttext');
        // a bit of cheating here :)
        $section->value['id'] = 1;
        $this->assertTrue($section->addParameter('test', 'test'));
    }

    function testShortTextValidation() {
        $section = CMS_Section::factory($this->page, 'type', 'shorttext');
        $data = array(
            'text' => 'none'
        );
        $this->assertTrue($section->validate_section($data));
    }

    function testShortTextSave() {
        $section = CMS_Section::factory($this->page, 'type', 'shorttext');
        $data = array(
            'type_key' => 1,
            'template_section_id' => 1
        );
        $this->assertTrue($section->save($data));
    }
    */

    /*
    function test() {
        $site = new CMS_Section($this->page);
        $site_array = array(
            'name' => 'Tester',
            'url' => 'http://localhost/',
            'cc_license' => '1'
        );
        $site->save($site_array);
        $this->assertEqual($site_array['name'], $site->get('name'));
        $this->assertEqual($site_array['url'], $site->get('url'));
        $this->assertEqual($site_array['cc_license'], $site->get('cc_license'));
    }
    */

}
?>