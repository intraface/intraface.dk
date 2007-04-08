<?php
require_once dirname(__FILE__) . '/../config.test.php';

require_once 'simpletest/unit_tester.php';
require_once 'simpletest/reporter.php';
require_once 'simpletest/mock_objects.php';

require_once 'Intraface/Weblogin.php';

class WebloginTestCase extends UnitTestCase {

	const SESSION_LOGIN = 'thissessionfirstlog';
	private $private_key;

	function __construct() {
		$this->private_key = md5('private' . date('d-m-Y H:i:s') . 'test');
		$this->public_key = md5('public' . date('d-m-Y H:i:s') . 'test');
		$db = MDB2::factory(DB_DSN);
		$db->exec('INSERT INTO intranet SET private_key = ' . $db->quote($this->private_key, 'text') . ', public_key = ' . $db->quote($this->public_key, 'text'));
	}

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
		$this->assertTrue($weblogin->auth('private', $this->private_key));

	}

	function testAuthWithCorrectPublicKey() {
		$weblogin = new Weblogin('sessidfsdfdsfdsfdsf');
		$this->assertTrue($weblogin->auth('public', $this->public_key));

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