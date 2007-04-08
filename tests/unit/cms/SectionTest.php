<?php
require_once dirname(__FILE__) . './../config.local.php';

require_once 'simpletest/unit_tester.php';
require_once 'simpletest/reporter.php';
require_once 'simpletest/mock_objects.php';

require_once PROJECT_PATH_ROOT . 'intraface.dk/config.local.php';
require_once PATH_INCLUDE . 'common.php';

class FakeUser {
	function get() {
		return 1;
	}
	function hasModuleAccess() {
		return true;
	}
}

class FakeIntranet {
	function get() {
		return 1;
	}
	function hasModuleAccess() {
		return true;
	}
}

class FakeSetting {
	function get() {
		return 1;
	}
	function set() {
		return true;
	}
}

class FakePage {
	public $kernel;
	function __construct($site) {
		$this->cmssite = $site;
		$this->kernel = $site->kernel;
	}
	function get() {
		return 1;
	}
}

class FakeSite {
	public $kernel;
	function __construct($kernel) {
		$this->kernel = $kernel;
	}
	function get() {
		return 1;
	}
}

/**
 * I have to make this more testable. At the moment
 * I have trouble with the Section Templates. Parameter is
 * also probably a bit of a problem.
 */

class SectionTestCase extends UnitTestCase {

	private $kernel;
	private $page;

	function setUp() {
		$this->kernel = new Kernel;
		$this->kernel->user = new FakeUser;
		$this->kernel->intranet = new FakeIntranet;
		$this->kernel->setting = new FakeSetting;
		$this->kernel->module('cms');
		$this->site = new FakeSite($this->kernel);
		$this->page = new FakePage($this->site);

	}

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
	/*
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
if (!isset($this)) {
	$test = new SectionTestCase;
	if (TextReporter::inCli()) {
		exit($test->run(new TextReporter()) ? 0 : 1);
	}
	$test->run(new HtmlReporter());
}
?>