<?php
if (session_id() == '') {
	session_start();
}

set_include_path(get_include_path() . PATH_SEPARATOR . 'c:\Documents and Settings\lars\workspace\Intraface\intraface/3Party/PEAR/');


require_once 'simpletest/unit_tester.php';
require_once 'simpletest/web_tester.php';
require_once 'simpletest/reporter.php';

require_once 'XML/RPC/RPC.php';

class TestNewsletterAPI extends WebTestCase {
    function testLocationOfNewsletter() {
		$this->assertTrue($this->get('http://www.intraface.dk/xmlrpc/newsletter/NewsletterServer.php'));
    }
}

/**
 * This test file should have tests for
 * - subscribe
 * - unsubscribe
 * - optin
 * - the e-mail sent
 */

class NewsletterTestCase extends UnitTestCase {

	protected $client;
	protected $credentials;
	protected $email_list;

	function __construct() {
		$this->client = new XML_RPC_Client('/xmlrpc/newsletter/NewsletterServer.php', 'www.intraface.dk');

		$this->client->setDebug(1);

		$this->credentials = new XML_RPC_Value(array(
			'private_key' => new XML_RPC_Value('sSXF97MECEPP7L9PicktTk7wZ6InP9b79gTqdNTWjQCyuSdCdFc', 'string'),
			'session_id' => new XML_RPC_Value(md5(session_id()), 'string')
			), 'struct');
		$this->email_list = 11;
		/*
		$resp = $this->client->send(new XML_RPC_Message('system.listMethods'));
		$val = $resp->value();
		$data = XML_RPC_decode($val);

		print_r($data);
		*/
	}

	function testSubscribe() {
		$params = array(
			$this->credentials,
			new XML_RPC_Value($this->email_list, 'int'),
			new XML_RPC_Value('lars@legestue.net', 'string'),
			new XML_RPC_Value($_SERVER['REMOTE_ADDR'], 'string')
		);
		/*
		$params = array(
			array('private_key' => 'sSXF97MECEPP7L9PicktTk7wZ6InP9b79gTqdNTWjQCyuSdCdFc', 'session_id' => md5(session_id())),
			$this->email_list,
			'lars@legestue.net',
			'127.0.0.0'
		);

		$params = XML_RPC_encode($params);
		*/


		print_r($params);
		$msg = new XML_RPC_Message('subscriber.subscribe', $params);
		$resp = $this->client->send($msg);

		//print_r($resp);

		// testing to see if there is a response
		$this->assertTrue($resp);

		if (!$resp) {
			die('No response from the server');
		}

		if ($resp->faultCode()) {
		    echo 'Fault Code: ' . $resp->faultCode() . "\n";
    		echo 'Fault Reason: ' . $resp->faultString() . "\n";
    		die();
		}
		$val = $resp->value();
		$data = XML_RPC_decode($val);
		$this->assertTrue($data);
	}
	function _testUnSubscribe() {
		$params = array(
			$this->credentials,
			new XML_RPC_Value($this->email_list, 'int'),
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
}
/*
if (TextReporter::inCli()) {
	exit ($test->run(new TextReporter()) ? 0 : 1);
}
$test = new  NewsletterTestCase();
$test->run(new HtmlReporter());
*/
?>