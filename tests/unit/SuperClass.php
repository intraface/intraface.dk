<?php
require_once dirname(__FILE__) . '/./config.local.php';

require_once 'simpletest/unit_tester.php';
require_once 'simpletest/reporter.php';
require_once 'simpletest/mock_objects.php';

require_once PATH_INCLUDE . 'common.php';
require_once PATH_INCLUDE_COMMON . 'core/Kernel.php';
require_once PATH_INCLUDE_COMMON . 'core/Intranet.php';
require_once PATH_INCLUDE_COMMON . 'core/User.php';

class MockSession extends Session {
	function start() {
		// void
	}
}


class SuperTestClass extends UnitTestCase {

	public $kernel;

	/*
	function __construct() {
		require_once PATH_PUBLIC . 'install/Install.php';

		$install = new Install;
		if (!$install->resetServer()) {
			die('could not reset server');
		}

	}
	*/

	function setUp() {

		// make sure configuration is staging configuration
		// setup database scheme
		// create users
		// add permissions
		$session = new MockSession;

		$this->kernel = new Kernel($session);
		$this->kernel->login('start@intraface.dk', 'startup');
		$this->kernel->isLoggedIn();

		$this->setUpChild();

	}

	function _testSetup() {
		$this->assertTrue(is_object($this->kernel));
		$this->assertTrue(is_object($this->kernel->intranet));
	}

	function setUpChild() {}




	function tearDown() {
		// tear down database
		//require PATH_PUBLIC . 'install/reset-staging-server.php';
		$this->kernel->logout();
	}

}

if (!isset($this) AND basename($_SERVER['PHP_SELF']) == 'SuperClass.php') {
	$test = new SuperTestClass();
	if (TextReporter::inCli()) {
		exit($test->run(new TextReporter()) ? 0 : 1);
	}
	$test->run(new HtmlReporter());
}
?>