<?php
require_once dirname(__FILE__) . '/../config.local.php';

require_once 'simpletest/unit_tester.php';
require_once 'simpletest/reporter.php';
require_once 'simpletest/mock_objects.php';

require_once PROJECT_PATH_ROOT . 'intraface.dk/config.local.php';
require_once PATH_INCLUDE . 'common.php';
require_once PATH_INCLUDE . 'common/core/User.php';

class UserTestCase extends UnitTestCase {

	function setUp() {
	}

	function testConstructionOfUser() {
		$user = new User(1);
		$this->assertTrue(is_object($user));
	}

	function testIntranetAccess() {
		$user = new User(1);
		$this->assertTrue($user->hasIntranetAccess(1));
		$this->assertFalse($user->hasIntranetAccess(2));
	}

	function testUserModuleAccess() {
		$user = new User(1);
		$this->assertFalse($user->hasModuleAccess('intranetmaintenance'));
		$this->assertFalse($user->hasModuleAccess('cms'));
		$user->setIntranetId(1); // spørgsmålet er om man bare skal have en init i stedet?
		$this->assertTrue($user->hasModuleAccess('intranetmaintenance'));
		$this->assertFalse($user->hasModuleAccess('cms'));

	}

	function testActiveIntranet() {
		$user = new User(1);
		$user->setIntranetId(1);

	}

	function testSetActiveIntranet() {
		$user = new User(1);
		$this->assertTrue($user->setActiveIntranetId(1));
	}


	function tearDown() {
	}
}
if (!isset($this)) {
	$test = new UserTestCase;
	if (TextReporter::inCli()) {
		exit($test->run(new TextReporter()) ? 0 : 1);
	}
	$test->run(new HtmlReporter());
}
?>