<?php
require_once dirname(__FILE__) . './../config.test.php';

require_once 'simpletest/unit_tester.php';
require_once 'simpletest/reporter.php';
require_once 'simpletest/mock_objects.php';

require_once 'Intraface/modules/contact/ContactReminder.php';


class FakeContact {
	private $id = 1;
	public function get() {
		return $this->id;
	}
}

class FakeIntranet {
	public function get() {
		return 1;
	}
}

class FakeKernel {
	public $intranet;
}

class ContactReminderTestCase extends UnitTestCase {

	private $kernel;

	function setUp() {
	}

	function getContact() {
		$kernel = new FakeKernel;
		$kernel->intranet = new FakeIntranet;
		return new FakeContact($kernel);
	}

	function testConstruction() {
		$reminder = new ContactReminder($this->getContact());
		$this->assertTrue(is_object($reminder));
	}

	function testSettingId() {
		$id = 2;
		$reminder = new ContactReminder($this->getContact(), $id);
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