<?php
/**
 * @package Debtor
 * @author  Lars Olesen <lars@legestue.net>
 * @since   0.1.0
 * @version @package-version@
 */

require_once 'XML/RPC2/Server.php';

class Intraface_XMLRPC_Debtor_Server {

    var $kernel;
    var $debtor;

    /**
     * Checks if user has credentials to ask server
     *
     * @param struct $credentials
     * @return true ved succes ellers object med fejlen
     */

    function checkCredentials($credentials) {

        if (count($credentials) != 2) {
            throw new XML_RPC2_FaultException('Der er et forkert antal argumenter i credentials', -2);
        }

        if (empty($credentials['private_key']) AND is_string($credentials['private_key'])) {
            throw new XML_RPC2_FaultException('Du skal skrive en kode', -2);
        }

        $this->kernel = new Kernel('weblogin');
        $this->kernel->weblogin('private', $credentials['private_key'], $credentials['session_id']);

        if (!is_object($this->kernel->intranet) AND get_class($this->kernel->intranet) != 'intranet') {
            throw new XML_RPC2_FaultException('Du har ikke adgang til intranettet', -2);
        }

        $debtor_module = $this->kernel->module('debtor');


    }

    /**
     * @param struct $credentials
     * @param integer $debtor_id
     */
    function getDebtor($credentials, $debtor_id) {
        if (is_object($return = $this->checkCredentials($credentials))) {
            return $return;
        }
        // die('her'.$arg[1].'gg');
        $debtor = Debtor::factory($this->kernel, $debtor_id);
        if (!$debtor->get('id') > 0) {
            return 0;
        }
        $debtor_info = array_merge($debtor->get());

        if (!$debtor_info) {
            return array();
        }

        return $debtor_info;
    }

    /**
     * @param struct $credentials
     * @param string $type
     * @param integer $contact_id
     */
    function getDebtorList($credentials, $type, $contact_id) {
        if (is_object($return = $this->checkCredentials($credentials))) {
            return $return;
        }

        $debtor = Debtor::factory($this->kernel, 0, $type);
        $debtor->dbquery->setFilter('contact_id', $contact_id);
          $debtor->dbquery->setFilter('status', '-1');
        return $debtor->getList();

    }

    /**
     * Alpha - will probably be deprecated shortly
     *
     * @param struct $credentials
     * @param integer $debtor_id
     */
    function getDebtorPdf($credentials, $debtor_id) {
        if (is_object($return = $this->checkCredentials($credentials))) {
            return $return;
        }
        $this->kernel->useShared('pdf');
        $debtor = Debtor::factory($this->kernel, $debtor_id);
        if (!$debtor->get('id') > 0) {
            return '';
        }

        $encoded = XML_RPC2_Value::createFromNative($debtor->pdf('string'), 'base64');
        return $encoded;

    }
    /*
    function setDebtorSent($credentials, $id) {
        if (is_object($return = $this->checkCredentials($credentials))) {
            return $return;
        }

        $debtor = Debtor::factory($this->kernel, $id);
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
?>
