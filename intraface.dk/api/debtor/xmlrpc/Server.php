<?php
/**
 * Bruges til at hente data via xml-rpc uden for selve intranettet.
 *
 * @author Lars Olesen <lars@legestue.net>
 */

require('../../XMLRPC/Server.php');
 
class API_Debtor_XMLRPC_Server extends API_XMLRPC_Server {

	var $kernel;
	var $debtor;

	function __construct() {
	
		$this->addCallback(
			'debtor.getList',
			'this:getList',
			array('boolean', 'struct'),
			''
		);	   

		$this->addCallback(
			'debtor.get',
			'this:get',
			array('boolean', 'struct'),
			''
		);	   

		$this->addCallback(
			'debtor.pdf',
			'this:pdf',
			array('boolean', 'struct'),
			''
		);	   

			   /*
			   'debtor.setSent' => 'this:setSent',
			   'debtor.createInvoice' => 'this:createInvoice',
			   'payment.capture' => 'this:capturePayment'					   			   
			   */
			   
		$this->serve();

		$debtor_module = $this->kernel->module('debtor');


    }



	/** 
	 * Metode til at tilmelde sig nyhedsbrevet
	 *
	 * @param struct $arg
	 * [0] $credentials
	 * [1] $id
	 */
	
	function get($arg) {
		if (count($arg) != 2) {
			return new IXR_Error(-4, 'Der er ikke det rigtige antal argumenter til get()');
		}

		if (is_object($return = $this->checkCredentials($arg[0]))) {
			return $return; 
		}
		// die('her'.$arg[1].'gg');
		$debtor = Debtor::factory($this->kernel, $arg[1]);
		if (!$debtor->get('id') > 0) {
			return 0;
		}
		$debtor_info = array_merge($debtor->get());
		
		if (!$debtor_info) {
			return array();
		}
		
		return $debtor_info;
	}  

	function getList($arg) {
		if (count($arg) != 3) {
			return new IXR_Error(-4, 'Der er ikke det rigtige antal argumenter til get()');
		}

		if (is_object($return = $this->checkCredentials($arg[0]))) {
			return $return; 
		}

		$debtor = new Debtor($this->kernel, $arg[1]);
		$debtor->dbquery->setFilter('contact_id', $arg[2]);
	  	$debtor->dbquery->setFilter("status", "-1");
		return $debtor->getList();

	}
	
	function pdf($arg) {

		if (count($arg) != 2) {
			return new IXR_Error(-4, 'Der er ikke det rigtige antal argumenter til pdf()');
		}

		if (is_object($return = $this->checkCredentials($arg[0]))) {
			return $return; 
		}
		$this->kernel->useModule('pdf');		
		$debtor = Debtor::factory($this->kernel, $arg[1]);
		if (!$debtor->get('id') > 0) {
			return '';
		}
		
		return new IXR_Base64($debtor->pdf('string'));
		
	}
	/*
	function setSent($arg) {
		if (is_object($return = $this->checkCredentials($arg[0]))) {
			return $return; 
		}

		$debtor = Debtor::factory($this->kernel, $arg[1]);
		if (!$debtor->get('id') > 0) {
			return '';
		}
		
		return $debtor->setStatus('sent');
	}
	
	function createInvoice($arg) {
		if (is_object($return = $this->checkCredentials($arg[0]))) {
			return $return; 
		}

		$order = Debtor::factory($this->kernel, $arg[1]);
		
		$invoice = new Invoice($this->kernel);
		if($id = $invoice->create($order)) {
			return $id;
		}
		return 0;
	}
	
	function capturePayment($arg) {
		if (is_object($return = $this->checkCredentials($arg[0]))) {
			return $return; 
		}
		
		$this->kernel->useModule('onlinepayment');
		
		$payment = OnlinePayment::factory($this->kernel, 'transactionnumber', $arg[1]);
		return $payment->transactionAction('capture');
	}
	*/
}
if($_SERVER['REQUEST_METHOD'] != 'POST' || $_SERVER['CONTENT_TYPE'] == 'application/x-www-form-urlencoded') {
	require('../Documentor.php');
	$doc = new XMLRPC_Documentor('http://www.intraface.dk' . $_SERVER['PHP_SELF']);
	$doc->setDescription('
	');

	echo $doc->display();
}
else {
	$server = new API_Debtor_XMLRPC_Server();
}

?>
