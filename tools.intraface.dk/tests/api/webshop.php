<?php
require_once 'simpletest/unit_tester.php';
require_once 'simpletest/web_tester.php';
require_once 'simpletest/reporter.php';

require_once 'XML/RPC/RPC.php';

/**
 * This test file should have tests for
 * - subscribe
 * - unsubscribe
 * - optin
 * - the e-mail sent
 */

class WebshopTestCase extends UnitTestCase {

	protected $client;
	protected $credentials;

	function setUp() {
		$this->client = new XML_RPC_Client('/xmlrpc/webshop/server2.php', 'www.intraface.dk');
		//$this->client->setDebug(1);

		$this->credentials = new XML_RPC_Value(array(
			'private_key' => new XML_RPC_Value('sSXF97MECEPP7L9PicktTk7wZ6InP9b79gTqdNTWjQCyuSdCdFc', 'string'),
			'session_id' => new XML_RPC_Value('thisisastupidstring', 'string')
			), 'struct');

	}

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


class TestWebshopAPI extends WebTestCase {

    function testLocationOfNewsletter() {
		$this->assertTrue($this->get('http://www.intraface.dk/xmlrpc/webshop/server.php'));
    }

}
?>