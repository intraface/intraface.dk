<?php
require_once dirname(__FILE__) . './../config.local.php';

require_once 'simpletest/unit_tester.php';
require_once 'simpletest/reporter.php';
require_once 'simpletest/mock_objects.php';

require_once PROJECT_PATH_ROOT . 'intraface.dk/config.local.php';
require_once PATH_INCLUDE . 'common.php';

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

class SiteTestCase extends UnitTestCase {

	private $kernel;

	function setUp() {
		$this->kernel = new Kernel;
		$this->kernel->intranet = new FakeIntranet;
		$this->kernel->setting = new FakeSetting;
		$this->kernel->module('cms');

	}

	function testSaveSucceedsWithValidValues() {
		$site = new CMS_Site($this->kernel);
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


}
if (!isset($this)) {
	$test = new SiteTestCase;
	if (TextReporter::inCli()) {
		exit($test->run(new TextReporter()) ? 0 : 1);
	}
	$test->run(new HtmlReporter());
}
?>