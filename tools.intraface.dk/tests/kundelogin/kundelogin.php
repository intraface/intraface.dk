<?php
if (file_exists(dirname(__FILE__) . '/configuration.local.php')) {
	require_once dirname(__FILE__) . '/configuration.local.php';
}
else {
	require_once dirname(__FILE__) . '/configuration.php';
}

require_once 'simpletest/unit_tester.php';
require_once 'simpletest/web_tester.php';
require_once 'simpletest/reporter.php';

class TestKundelogin extends WebTestCase {

	/*
	protected $url;
	protected $subdomain;
	protected $handle;
	*/

    function testIfFrontpageIsReturningAnErrorWithNoSubdomain() {
		$this->assertTrue($this->get(URL));
		$this->assertText('Error');
    }

    function testToSeeIfWeCanLogin() {
		$this->assertTrue($this->get(SUBDOMAIN));
		$this->assertField('handle', '');
		$this->setField('handle', HANDLE);
		$this->clickSubmit('Login');
		$this->assertText('Velkommen');
    }

}
?>