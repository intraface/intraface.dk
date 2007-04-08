<?php
require_once dirname(__FILE__) . '/../config.test.php';

require_once 'simpletest/unit_tester.php';
require_once 'simpletest/reporter.php';
require_once 'simpletest/mock_objects.php';

require_once 'Intraface/Kernel.php';
require_once 'Intraface/Weblogin.php';

class KernelTestCase extends UnitTestCase {
	function testRandomKey() {
		$this->assertTrue(strlen(Kernel::randomKey(9)) == 9);
	}

	function testWeblogin() {
		$session_id = 'sesssdfion';
		$kernel = new Kernel;
		$this->assertFalse($kernel->weblogin('private', 'wrongkey', $session_id));
		$this->assertTrue($kernel->weblogin('private', md5('private_key'), $session_id));
		$this->assertEqual(get_class($kernel->weblogin), 'Weblogin');
		$this->assertEqual(get_class($kernel->intranet), 'Intranet');
		$this->assertEqual(get_class($kernel->setting), 'Setting');
		$this->assertTrue($kernel->weblogin('public', md5('public_key'), $session_id));
		$this->assertEqual(get_class($kernel->weblogin), 'Weblogin');
		$this->assertEqual(get_class($kernel->intranet), 'Intranet');
		$this->assertEqual(get_class($kernel->setting), 'Setting');

	}


	function tearDown() {

		//$this->session = null;
	}

}
if (!isset($this)) {
	$test = new KernelTestCase;
	if (TextReporter::inCli()) {
		exit($test->run(new TextReporter()) ? 0 : 1);
	}
	$test->run(new HtmlReporter());
}
?>