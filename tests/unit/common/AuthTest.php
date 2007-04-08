<?php
require_once dirname(__FILE__) . '/../config.local.php';

require_once 'simpletest/unit_tester.php';
require_once 'simpletest/reporter.php';
require_once 'simpletest/mock_objects.php';

require_once PROJECT_PATH_ROOT . 'intraface.dk/config.local.php';
require_once PATH_INCLUDE . 'common.php';
require_once PATH_INCLUDE . 'common/core/Auth.php';

class FakeObserver {}

class AuthTestCase extends UnitTestCase {

	const SESSION_LOGIN = 'thissessionfirstlog';

	function setUp() {
		$auth = new Auth(self::SESSION_LOGIN);
		if ($auth->isLoggedIn()) {
			$auth->logout();
		}
	}

	function testConstructionOfAuth() {
		$auth = new Auth(self::SESSION_LOGIN);
		$this->assertTrue(is_object($auth));
	}

	function testLoginFailsOnIncorrectCredentials() {
		$auth = new Auth(self::SESSION_LOGIN);
		$this->assertFalse($auth->login('incorrect@email.dk', 'incorrectpass'));
		$this->assertFalse($auth->isLoggedIn());
		$this->assertFalse($auth->login('incorrect@email.dk', 'startup'));
		$this->assertFalse($auth->isLoggedIn());
		$this->assertFalse($auth->login('start@intraface.dk', 'incorrectpass'));
		$this->assertFalse($auth->isLoggedIn());

	}

	function testLoginSucceedsOnCorrectCredentials() {
		$auth = new Auth(self::SESSION_LOGIN);
		$this->assertTrue($auth->login('start@intraface.dk', 'startup'));
		$this->assertTrue($auth->isLoggedIn());
	}

	function testLogout() {
		$auth = $this->createLoggedInAuth();
		$this->assertTrue($auth->logout());
		$this->assertFalse($auth->isLoggedIn());
	}


	function testChangeOfSessionIsNotLoggedIn() {
		$auth = $this->createLoggedInAuth();
		$auth = new Auth('anotherdifferntsession');
		$this->assertFalse($auth->isLoggedIn());
	}

	function createLoggedInAuth() {
		$auth = new Auth(self::SESSION_LOGIN);
		$auth->login('start@intraface.dk', 'startup');
		$auth->isLoggedIn();
		return $auth;
	}

	function testAttach() {
		$auth = new Auth('session');
		$auth->attachObserver(new FakeObserver);
		$observers = $auth->getObservers();
		$this->assertTrue(count($observers) == 1);
		// this assert is a
	}

	function tearDown() {
		$auth = new Auth(self::SESSION_LOGIN);
		if ($auth->isLoggedIn()) {
			$auth->logout();
		}

	}
}
if (!isset($this)) {
	$test = new AuthTestCase;
	if (TextReporter::inCli()) {
		exit($test->run(new TextReporter()) ? 0 : 1);
	}
	$test->run(new HtmlReporter());
}
?>