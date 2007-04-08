<?php
require_once dirname(__FILE__) . '/../config.test.php';

require_once 'simpletest/unit_tester.php';
require_once 'simpletest/reporter.php';
require_once 'simpletest/mock_objects.php';

require_once 'Intraface/Setting.php';

class SettingTestCase extends UnitTestCase {

	function setUp() {
	}

	function testConstructionOfUser() {
		$setting = new Setting(1, 1);
		$this->assertTrue(is_object($setting));
	}

	function testGetUserSettingSetting() {
		$setting = new Setting(1, 1);
		$setting->set('intranet', 'rows_pr_page', 15);
		$setting->set('user', 'rows_pr_page', 10);
		$this->assertEqual(20, $setting->get('system', 'rows_pr_page'));
		$this->assertEqual(15, $setting->get('intranet', 'rows_pr_page'));
		$this->assertEqual(10, $setting->get('user', 'rows_pr_page'));
	}

	function tearDown() {
	}
}
if (!isset($this)) {
	$test = new SettingTestCase;
	if (TextReporter::inCli()) {
		exit($test->run(new TextReporter()) ? 0 : 1);
	}
	$test->run(new HtmlReporter());
}
?>