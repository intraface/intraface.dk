<?php
require_once dirname(__FILE__) . './../config.test.php';

require_once 'simpletest/unit_tester.php';
require_once 'simpletest/reporter.php';
require_once 'simpletest/mock_objects.php';

require_once 'Intraface/modules/contact/Contact.php';


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

class ContactTestCase extends UnitTestCase {

	private $kernel;

	function setUp() {
	}

	function getKernel() {
		$kernel = new FakeKernel;
		$kernel->intranet = new FakeIntranet;
		return $kernel;
	}

	function testConstruction() {
		$contact = new Contact($this->getKernel());
		$this->assertTrue(is_object($contact));
	}

	function testNeedOptin() {
		$contact = new Contact($this->getKernel(), 7);
		$array = $contact->needNewsletterOptin();
		$this->assertTrue(is_array($array));

	}

}
if (!isset($this)) {
	$test = new ContactTestCase;
	if (TextReporter::inCli()) {
		exit($test->run(new TextReporter()) ? 0 : 1);
	}
	$test->run(new HtmlReporter());
}
?>