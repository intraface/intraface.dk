<?php
require_once dirname(__FILE__) . '/../config.local.php';

require_once 'simpletest/unit_tester.php';
require_once 'simpletest/reporter.php';
require_once 'simpletest/mock_objects.php';

require_once PROJECT_PATH_ROOT . 'intraface.dk/config.local.php';
require_once PATH_INCLUDE . 'common.php';
require_once PATH_INCLUDE . 'common/core/Weblogin.php';

class WebloginTestCase extends UnitTestCase {

	const SESSION_LOGIN = 'thissessionfirstlog';

	function testConstructionOfWeblogin() {
		$weblogin = new Weblogin(self::SESSION_LOGIN);
		$this->assertTrue(is_object($weblogin));
	}

	function testAuthWithWrongPrivateKey() {
		$weblogin = new Weblogin('sessidfsdfdsfdsfdsf');
		$this->assertFalse($weblogin->auth('private', 'wrongkey'));
	}

	function testAuthWithWrongPublicKey() {
		$weblogin = new Weblogin('sessidfsdfdsfdsfdsf');
		$this->assertFalse($weblogin->auth('public', 'wrongkey'));
	}


	function testAuthWithCorrectPrivateKey() {
		$weblogin = new Weblogin('sessidfsdfdsfdsfdsf');
		$this->assertTrue($weblogin->auth('private', md5('private_key')));

	}

	function testAuthWithCorrectPublicKey() {
		$weblogin = new Weblogin('sessidfsdfdsfdsfdsf');
		$this->assertTrue($weblogin->auth('public', md5('public_key')));

	}

}
if (!isset($this)) {
	$test = new WebloginTestCase;
	if (TextReporter::inCli()) {
		exit($test->run(new TextReporter()) ? 0 : 1);
	}
	$test->run(new HtmlReporter());
}
?>