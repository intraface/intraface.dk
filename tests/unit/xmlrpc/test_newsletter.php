<?php
require_once dirname(__FILE__) . '/../config.local.php';

require_once 'simpletest/unit_tester.php';
require_once 'simpletest/web_tester.php';
require_once 'simpletest/reporter.php';

require_once 'XML/RPC2/Client.php';

class NewsletterTestCase extends UnitTestCase {

	protected $client;
	protected $credentials;
	protected $methods;

	function setUp() {
		$this->client = XML_RPC2_Client::create(PATH_XMLRPC . 'webshop/server2.php', array('prefix' => 'system.'));
		$this->methods = array(
			'list.getList' => '',
			'subscriber.needOptin' => '',
			'subscriber.getSubscriptions' => '',
			'subscriber.optin' => '',
			'subscriber.unsubscribe' => '',
			'subscriber.subscribe' => '');
		$this->system_methods = array(
			'system.methodHelp' => '',
			'system.methodSignature' => '',
			'system.multicall' => '',
			'system.listMethods' => '',
			'system.getCapabilities' => '');

		$this->credentials = array(
			'private_key' => 'sSXF97MECEPP7L9PicktTk7wZ6InP9b79gTqdNTWjQCyuSdCdFc',
			'session_id' => 'thisisastupidstring'
			);

	}

	function createClient($prefix) {
		return XML_RPC2_Client::create(PATH_XMLRPC . 'newsletter/NewsletterServer.php', array('prefix' => $prefix));
	}

	function testServerFunctions() {
		$client = $this->createClient('system.');
		//print_r($client->listMethods());
		$methods = array_merge(array_keys($this->methods), array_keys($this->system_methods));
		$this->assertEqual($methods, $client->listMethods());
	}

	function testWrongNumberOfMethodParameters() {
		$test = '';

		foreach ($this->methods AS $method => $something) {
			$pieces = explode('.', $method);
			if ($pieces[0] != $test) {
				$client = $this->createClient($pieces[0] . '.');
				$test = $pieces[0];
			}
			try {
				$result = $client->$pieces[1]();
			}
			catch (XML_RPC2_FaultException $e) {
				$this->assertTrue('server error. wrong number of method parameters' == $e->getFaultString());
			}
		}

	}
	/*
	function testWrongCredentials() {
		$test = '';

		foreach ($this->methods AS $method => $something) {
			$pieces = explode('.', $method);
			if ($pieces[0] != $test) {
				$client = $this->createClient($pieces[0] . '.');
				$test = $pieces[0];
			}
			try {
				// find out how I can make the array so I also now which credentials
				$result = $client->$pieces[1]($this->credentials);
			}
			catch (XML_RPC2_FaultException $e) {
				$this->assertTrue($e->getFaultCode() == -2);
			}
		}
	}
	*/
}


if (!isset($this)) {
	$test = &new NewsletterTestCase();
	if (TextReporter::inCli()) {
		exit($test->run(new TextReporter()) ? 0 : 1);
	}
	$test->run(new HtmlReporter());
}

?>