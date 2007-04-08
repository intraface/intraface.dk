<?php
require_once dirname(__FILE__) . '/../config.test.php';

require_once 'simpletest/unit_tester.php';
require_once 'simpletest/reporter.php';

require_once 'Intraface/XMLRPC/Contact/Server.php';

class ContactXMLRPCTestCase extends UnitTestCase {

	protected $client;
	protected $credentials;
	protected $methods;
	private $private_key;
	private $contact_key;

	function __construct() {
		$this->private_key = md5('private' . date('d-m-Y H:i:s') . 'test');
		$this->public_key = md5('public' . date('d-m-Y H:i:s') . 'test');
		$db = MDB2::factory(DB_DSN);
		$db->exec('INSERT INTO intranet SET private_key = ' . $db->quote($this->private_key, 'text') . ', public_key = ' . $db->quote($this->public_key, 'text'));

		$this->contact_key = md5('contact_key' . date('Y-m-d H:i:s') . 'test');
		$db = MDB2::factory(DB_DSN);
		$db->exec('INSERT INTO contact
			SET
				code = ' . $db->quote($contact_key, 'text'));

	}

	function testCheckCredentials() {
		$credentials = array(
			'private_key' => $this->private_key,
			'session_id' => 'somesessionid'
		);
		$server = new Intraface_XMLRPC_Contact;
		$this->assertTrue($server->checkCredentials($credentials));
	}

	function testAuthenticateContact() {
		$credentials = array(
			'private_key' => $this->private_key,
			'session_id' => 'somesessionid'
		);

		$server = new Intraface_XMLRPC_Contact;
		$this->assertTrue($server->authenticateContact($credentials, $this->contact_key));
	}


	function testGetContact() {
		$credentials = array(
			'private_key' => $this->private_key,
			'session_id' => 'somesessionid'
		);
		$server = new Intraface_XMLRPC_Contact;
		$this->assertTrue($server->checkCredentials($credentials));
	}

	function testSaveContact() {
		$credentials = array(
			'private_key' => $this->private_key,
			'session_id' => 'somesessionid'
		);
		$server = new Intraface_XMLRPC_Contact;
		$this->assertTrue($server->checkCredentials($credentials));
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