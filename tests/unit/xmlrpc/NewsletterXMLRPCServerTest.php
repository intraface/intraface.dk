<?php
require_once dirname(__FILE__) . '/../config.test.php';

require_once 'simpletest/unit_tester.php';
require_once 'simpletest/reporter.php';

require_once 'Intraface/XMLRPC/Newsletter/Server.php';

class FakeKernel {
	public $intranet;
	public $setting;
}

class FakeSetting {
	function get() {
		return '';
	}
}
class FakeIntranet {
	private $id;
	function __construct($id) {
		$this->id = $id;
	}
	function get() {
		return $this->id;
	}
	function hasModuleAccess() {
		return true;
	}
}

class NewsletterXMLRPCTestCase extends UnitTestCase {

	protected $client;
	protected $credentials;
	protected $methods;
	private $kernel;
	private $private_key;
	private $contact_key;
	private $insert_id;

	function __construct() {
		$this->private_key = md5('private' . date('d-m-Y H:i:s') . 'test');
		$this->public_key = md5('public' . date('d-m-Y H:i:s') . 'test');
		$db = MDB2::factory(DB_DSN);
		$db->exec('INSERT INTO intranet SET private_key = ' . $db->quote($this->private_key, 'text') . ', public_key = ' . $db->quote($this->public_key, 'text'));
		$intranet_id = $db->lastInsertId();

		$this->contact_key = md5('contact_key' . date('Y-m-d H:i:s') . 'test');
		$db = MDB2::factory(DB_DSN);
		$result = $db->exec('INSERT INTO contact
			SET
				intranet_id = '.$db->quote($intranet_id, 'integer').',
				password = ' . $db->quote($this->contact_key, 'text'));

		$this->insert_id = $db->lastInsertId();
		$this->kernel = new FakeKernel;
		$this->kernel->intranet = new FakeIntranet($intranet_id);
		$this->kernel->setting = new FakeSetting;
	}

	function testCheckCredentialsWithoutKernel() {
		$credentials = array(
			'private_key' => $this->private_key,
			'session_id' => 'somesessionid'
		);
		$server = new Intraface_XMLRPC_Newsletter_Server;
		$this->assertTrue($server->checkCredentials($credentials));
	}

	function testGetNewsletterList() {
		$credentials = array(
			'private_key' => $this->private_key,
			'session_id' => 'somesessionid'
		);

		$server = new Intraface_XMLRPC_Newsletter_Server();
		$this->assertTrue(is_array($array = $server->getNewsletterList($credentials)));
		print_r($array);

	}
	function testGetSubscriptions() {
		$credentials = array(
			'private_key' => $this->private_key,
			'session_id' => 'somesessionid'
		);

		$server = new Intraface_XMLRPC_Newsletter_Server();
		$this->assertTrue(is_array($array = $server->getSubscriptions($credentials, 1)));
		print_r($array);

	}
}

if (!isset($this)) {
	$test = new NewsletterXMLRPCTestCase();
	if (TextReporter::inCli()) {
		exit($test->run(new TextReporter()) ? 0 : 1);
	}
	$test->run(new HtmlReporter());
}
?>