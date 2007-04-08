<?php
require_once dirname(__FILE__) . './../config.local.php';

require_once 'simpletest/unit_tester.php';
require_once 'simpletest/reporter.php';
require_once 'simpletest/mock_objects.php';

require_once PROJECT_PATH_ROOT . 'intraface.dk/config.local.php';
require_once PATH_INCLUDE . 'common.php';
require_once PATH_INCLUDE . 'modules/contact/ContactReminder.php';


class FakeContact {
	private $id = 1;
	public function get() {
		return $this->id;
	}

}

class ContactReminderTestCase extends UnitTestCase {

	private $kernel;

	function setUp() {
	}

	function testConstruction() {
		$reminder = new ContactReminder(new FakeContact());
		$this->assertTrue(is_object($reminder));
	}

	function testSettingId() {
		$id = 2;
		$reminder = new ContactReminder(new FakeContact, $id);
		$this->assertEqual($id, $reminder->get('id'));
	}

	function testProductCanGetNumberIfOtherProductDontNeedItAnymore() {
	}


}
if (!isset($this)) {
	$test = new ContactReminderTestCase;
	if (TextReporter::inCli()) {
		exit($test->run(new TextReporter()) ? 0 : 1);
	}
	$test->run(new HtmlReporter());
}
?>