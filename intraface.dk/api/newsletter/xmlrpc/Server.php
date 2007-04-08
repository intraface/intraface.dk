<?php
/**
 * @author Lars Olesen <lars@legestue.net>
 */

require('../../XMLRPC/Server.php');

class API_Newsletter_XMLRPC_Server extends API_XMLRPC_Server {

	var $kernel;
	var $list;
	var $subscriber;
	var $credentials;

	function __construct() {
		parent::__construct();

		$this->addCallback(
			'subscriber.subscribe',
			'this:subscribe',
			array('boolean', 'struct', 'integer', 'string', 'string'),
			'Returns true / false'
		);

		$this->addCallBack(
			'subscriber.unsubscribe',
			'this:unsubscribe',
			array('boolean', 'struct', 'integer', 'string'),
			'Returns true / false'
		);

		$this->addCallBack(
			'subscriber.optin',
			'this:optin',
			array('boolean', 'struct', 'integer', 'string', 'string'),
			'Returns true / false'
		);

		$this->addCallBack(
			'subscriber.getSubscriptions',
			'this:getSubscriptions',
			array('array', 'struct', 'integer'),
			'Returns array with all subscriptions'
		);

		$this->addCallBack(
			'subscriber.needOptin',
			'this:needOptin',
			array('array', 'struct', 'integer'),
			'Returns array with all newslists with no optin'
		);

		$this->addCallBack(
			'list.getList',
			'this:getList',
			array('array', 'struct'),
			'Returns array with all available lists (only optional lists)'
		);

		$this->serve();
	}

	function factoryList($list_id) {
		$this->kernel->useModule('newsletter');

		$this->list = new NewsletterList($this->kernel, $list_id);

		if (!$this->list->doesListExist()) {
			return new IXR_Error(-2, 'Listen eksisterer ikke');
		}


		$this->subscriber = new NewsletterSubscriber($this->list);

	}

	/**
	 * Metode til at tilmelde sig nyhedsbrevet
	 *
	 * @param struct $arg
	 * [0] $credentials
	 * [1] $email
	 * [2] $ip
	 */

	function subscribe($arg) {
		$credentials = $arg[0];
		$list_id = $arg[1];
		$email = $arg[2];
		$ip = $arg[3];

		if (is_object($return = $this->checkCredentials($arg[0]))) {
			return $return;
		}

		$this->factoryList($arg[1]);

		if (!$this->subscriber->subscribe(array('email'=>$arg[2], 'ip'=>$arg[3]))) {
			return new IXR_Error(-4, 'Du kunne ikke tilmelde dig ' .$arg[2]);
		}

		return 1;
	}

	/**
	 * Metode til at tilmelde sig nyhedsbrevet
	 *
	 * @param struct $arg
	 * [0] $credentials
	 * [1] $email
	 */

	function unsubscribe($arg) {
		if (is_object($return = $this->checkCredentials($arg[0]))) {
			return $return;
		}

		$this->factoryList($arg[1]);

		if (!$this->subscriber->unsubscribe($arg[2])) {
			return new IXR_Error(-4, 'Du kunne ikke framelde dig ' .$arg[1]);
		}

		return 1;
	}

	/**
	 * Metode til at tilmelde sig nyhedsbrevet
	 *
	 * @param struct $arg
	 * [0] $credentials
	 * [1] $optincode
	 * [2] $ip
	 */
	function optin($arg) {
		$credentials = $arg[0];

		if (is_object($return = $this->checkCredentials($credentials))) {
			return $return;
		}

		$this->factoryList($arg[1]);

		$optin_code = $arg[2];
		$ip = $arg[3];

		if (!$this->subscriber->optIn($optin_code, $ip)) {
			return new IXR_Error(-4, 'Du kunne ikke bekræfte din tilmelding');
		}

		return 1;

	}

	function getList($arg) {

		$credentials = $arg;

		if (is_object($return = $this->checkCredentials($credentials))) {
			return $return;
		}

		if (!$this->kernel->intranet->hasModuleAccess('newsletter')) {
			return array();
		}
		$this->kernel->module('newsletter');

		$list = new NewsletterList($this->kernel);

		return $list->getList();
	}

	function getSubscriptions($arg) {
		$credentials = $arg[0];

		if (is_object($return = $this->checkCredentials($credentials))) {
			return $return;
		}
		$this->kernel->useModule('contact');

		$contact = new Contact($this->kernel, $arg[1]);
		return $contact->getNewsletterSubscriptions();

	}

	function needOptin($arg) {
		$credentials = $arg[0];

		if (is_object($return = $this->checkCredentials($credentials))) {
			return $return;
		}
		$this->kernel->useModule('contact');

		$contact = new Contact($this->kernel, $arg[1]);
		return $contact->needNewsletterOptin();

	}

}
if($_SERVER['REQUEST_METHOD'] != 'POST' || $_SERVER['CONTENT_TYPE'] == 'application/x-www-form-urlencoded') {
	require('../Documentor.php');
	$doc = new XMLRPC_Documentor('http://www.intraface.dk' . $_SERVER['PHP_SELF']);
	$doc->setDescription('
	');

	echo $doc->display();
}
else {
	$server = new API_Newsletter_XMLRPC_Server();
}

?>