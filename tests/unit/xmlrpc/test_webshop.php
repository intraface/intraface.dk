<?php
require_once dirname(__FILE__) . '/../config.local.php';

require_once 'simpletest/unit_tester.php';
require_once 'simpletest/web_tester.php';
require_once 'simpletest/reporter.php';

require_once 'XML/RPC2/Client.php';
require_once 'MDB2.php';

class WebshopTestCase extends UnitTestCase {

	protected $credentials;
	protected $methods = array(
			'payment.addOnlinePayment' => array('boolean', 'struct', 'integer', 'string', 'string', 'double'),
			'basket.placeOrder' => array('boolean', 'struct', 'array'),
			'basket.get' => array('array', 'struct'),
			'basket.getItems' => array('array', 'struct'),
			'basket.totalWeight' => array('integer', 'struct'),
			'basket.totalPrice' => array('float', 'struct'),
			'basket.change' => array('boolean', 'struct', 'integer', 'integer'),
			'basket.add' => array('boolean', 'struct', 'integer'),
			'products.getRelatedProducts' => array('array', 'struct', 'integer'),
			'products.getProduct' => array('array', 'struct', 'integer'),
			'products.getList' => array('array, struct, array'));
	protected $system_methods = array(
			'system.methodHelp' => '',
			'system.methodSignature' => '',
			'system.multicall' => '',
			'system.listMethods' => '',
			'system.getCapabilities' => '');

	function setUp() {



	}

	function getCredentials() {
		$db = MDB2::singleton(DB_DSN);
		if (PEAR::isError($db)) {
			die($db->getUserInfo());
		}
		$result = $db->query("SELECT intranet.private_key FROM intranet INNER JOIN product ON intranet.id = product.intranet_id WHERE product.id <> '' LIMIT 1");
		if (PEAR::isError($result)) {
			die($result->getUserInfo());
		}
		if ($result->numRows() == 0) die('cannot run test - no products anywhere');
		$row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
		return array(
			'private_key' => $row['private_key'],
			'session_id' => 'thisisastupidstring'
		);
	}

	function createClient($prefix) {
		return XML_RPC2_Client::create(PATH_XMLRPC . 'webshop/server2.php', array('prefix' => $prefix));
	}

	/*
	function createMethodCall($method, $arguments) {
		foreach ($arguments AS $argument) {
			switch ($argument) {
				case 'struct':
				break;
				case 'string':
				break;
				case 'integer':
				break;
			}
		}
		return $method();
	}
	*/

	function testServerFunctions() {
		$client = $this->createClient('system.');
		$methods = array_merge(array_keys($this->methods), array_keys($this->system_methods));
		$this->assertEqual($methods, $client->listMethods());
	}

	function testWrongNumberOfMethodParametersReturnsAnError() {
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

	function testEmptyCredentialsReturnsAnError() {
		$client = $this->createClient('basket.');

		try {
			$result = $client->get(array('private_key' => '', 'session_id' => ''));
		}
		catch (XML_RPC2_FaultException $e) {
			$this->assertTrue($e->getFaultCode() == -5);
		}
	}

	function testWrongCredentialsReturnsAnError() {
			$client = $this->createClient('basket.');

			try {
				$result = $client->get(array('private_key' => 'wrongcredentials', 'session_id' => 'somestring'));
			}
			catch (XML_RPC2_FaultException $e) {
				$this->assertTrue($e->getFaultCode() == -2);
			}

		/*
		$test = '';

		foreach ($this->methods AS $method => $signature) {
			$pieces = explode('.', $method);
			if ($pieces[0] != $test) {
				$client = $this->createClient($pieces[0] . '.');
				$test = $pieces[0];
			}
			try {
				$result = $client->$pieces[1]($this->credentials);
			}
			catch (XML_RPC2_FaultException $e) {
				$this->assertTrue($e->getFaultCode() == -2);
			}
		}
		*/
	}

	/*
	function testGetProducts() {

		// getting products
		$params = array(
			$this->credentials,
			new XML_RPC_Value('', 'string')
		);

		$msg = new XML_RPC_Message('products.getList', $params);
		$resp = $this->client->send($msg);
		if (!$resp) die('no response');
		if ($resp->faultCode()) {
		    echo 'Fault Code: ' . $resp->faultCode() . "\n";
    		echo 'Fault Reason: ' . $resp->faultString() . "\n";
    		die();
		}
		$val = $resp->value();
		$data = XML_RPC_decode($val);
		$this->assertEqual(gettype($data), 'array');

		// getting one product
		if (count($data['products']) == 0) die('No live products - cannot test');

		$params = array(
			$this->credentials,
			new XML_RPC_Value($data['products'][0]['id'], 'int')
		);

		$msg = new XML_RPC_Message('products.getProduct', $params);
		$resp = $this->client->send($msg);
		if (!$resp) die('no response');
		if ($resp->faultCode()) {
		    echo 'Fault Code: ' . $resp->faultCode() . "\n";
    		echo 'Fault Reason: ' . $resp->faultString() . "\n";
    		die();
		}
		$val = $resp->value();
		$data = XML_RPC_decode($val);
		$this->assertTrue(gettype($data), 'array');

		$product_id = $data['id'];

		// adding product to basket
		// there should be made some prefix to this
		// as it would fail if no product is in stock
		// need to make two test - one where I am certain there
		// is enough on stock and one without stock
		$params = array(
			$this->credentials,
			new XML_RPC_Value($product_id, 'int')
		);

		$msg = new XML_RPC_Message('basket.add', $params);
		$resp = $this->client->send($msg);
		if (!$resp) die('no response');
		if ($resp->faultCode()) {
		    echo 'Fault Code: ' . $resp->faultCode() . "\n";
    		echo 'Fault Reason: ' . $resp->faultString() . "\n";
    		die();
		}
		$val = $resp->value();
		$data = XML_RPC_decode($val);
		$this->assertTrue($data);

		// removing
		$params = array(
			$this->credentials,
			new XML_RPC_Value('1', 'int')
		);

		$msg = new XML_RPC_Message('basket.add', $params);
		$resp = $this->client->send($msg);
		if (!$resp) die('no response');
		if ($resp->faultCode()) {
		    echo 'Fault Code: ' . $resp->faultCode() . "\n";
    		echo 'Fault Reason: ' . $resp->faultString() . "\n";
    		die();
		}
		$val = $resp->value();
		$data = XML_RPC_decode($val);
		$this->assertTrue($data);

	}
	*/
	/*
	function testUnSubscribe() {
		$params = array(
			$this->credentials,
			new XML_RPC_Value(20, 'int'),
			new XML_RPC_Value('lars@legestue.net', 'string')
		);
		//print_r($params);

		$msg = new XML_RPC_Message('subscriber.unsubscribe', $params);
		$resp = $this->client->send($msg);
		if (!$resp) die('no response');
		if ($resp->faultCode()) {
		    echo 'Fault Code: ' . $resp->faultCode() . "\n";
    		echo 'Fault Reason: ' . $resp->faultString() . "\n";
    		die();
		}
		$val = $resp->value();
		$data = XML_RPC_decode($val);
		$this->assertTrue($data);
	}
	*/
}


if (!isset($this)) {
	$test = &new WebshopTestCase();
	if (TextReporter::inCli()) {
		exit($test->run(new TextReporter()) ? 0 : 1);
	}
	$test->run(new HtmlReporter());
}



?>