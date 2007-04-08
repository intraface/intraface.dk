<?php
require_once 'simpletest/unit_tester.php';
require_once 'simpletest/reporter.php';

require_once 'Intraface/XMLRPC/Contact/Server.php';

class ContactXMLRPCTestCase extends UnitTestCase {

	protected $client;
	protected $credentials;
	protected $methods;

	function testCheckCredentials() {
		$credentials = array(
			'private_key' => md5('private_key'),
			'session_id' => 'somesessionid'
		);
		$server = new Intraface_Contact;
		$server->checkCredentials($credentials);
	}

}

if (!isset($this)) {
	$test = new ContactXMLRPCTestCase();
	if (TextReporter::inCli()) {
		exit($test->run(new TextReporter()) ? 0 : 1);
	}
	$test->run(new HtmlReporter());
}
?>