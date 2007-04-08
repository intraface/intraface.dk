<?php
/**
 * Bruges til at hente data via xml-rpc uden for selve intranettet.
 *
 * @author Lars Olesen <lars@legestue.net>
 */

require '../../common.php';
require_once '3Party/IXR/IXR.php';

class DebtorServer extends IXR_Server {

	var $kernel;
	var $debtor;

	function DebtorServer() {
		$this->IXR_Server(array(
			   'debtor.get' => 'this:get',
			   'debtor.list' => 'this:getList',
			   'debtor.pdf' => 'this:pdf',
			   'debtor.setSent' => 'this:setSent',
			   'debtor.createInvoice' => 'this:createInvoice',
			   'payment.capture' => 'this:capturePayment'
        ));
    }

	/**
	 * Tjekker om forespørgslen må foretages
	 *
	 * @param struct $credentials
	 * 	- list_id = integer // nyhedsbrevlisten
	 *  - key_code = session_id
	 * @return true ved succes ellers object med fejlen
	 */

	function checkCredentials($credentials) {

		if (count($credentials) != 2) {
			return new IXR_Error(-2, 'Der er et forkert antal argumenter i credentials');
		}

		if (empty($credentials['private_key']) AND is_string($credentials['private_key'])) {
			return new IXR_Error(-2, 'Du skal skrive en kode');
		}

		$this->kernel = new Kernel('weblogin');
		$this->kernel->weblogin('private', $credentials['private_key'], $credentials['session_id']);

		if (!is_object($this->kernel->intranet) AND get_class($this->kernel->intranet) != 'intranet') {
			return new IXR_Error(-2, 'Du har ikke adgang til intranettet');
		}

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
		$this->kernel->useShared('pdf');
		$debtor = Debtor::factory($this->kernel, $arg[1]);
		if (!$debtor->get('id') > 0) {
			return '';
		}

		return new IXR_Base64($debtor->pdf('string'));

	}

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

}

$server = new DebtorServer();
?>
