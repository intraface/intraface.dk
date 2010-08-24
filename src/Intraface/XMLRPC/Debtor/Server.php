<?php
/**
 * @package Debtor
 * @author  Lars Olesen <lars@legestue.net>
 * @since   0.1.0
 * @version @package-version@
 */

class Intraface_XMLRPC_Debtor_Server_Translation
{
    function get($key)
    {
        return $key;
    }

    function setPageID() {}
}

class Intraface_XMLRPC_Debtor_Server extends Intraface_XMLRPC_Server0100
{
    /**
     * @var object
     */
    protected $kernel;

    /**
     * @var object
     */
    protected $debtor;

    /**
     * Checks if user has credentials to ask server
     *
     * @param struct $credentials Provided by intraface
     *
     * @return true ved succes ellers object med fejlen
     */
    protected function checkCredentials($credentials)
    {
        if (count($credentials) != 2) {
            require_once 'XML/RPC2/Exception.php';
            throw new XML_RPC2_FaultException('Wrong number of parameters.', -2);
        }

        if (empty($credentials['private_key'])) {
            require_once 'XML/RPC2/Exception.php';
            throw new XML_RPC2_FaultException('You must supply a private key.', -2);
        }

		$auth_adapter = new Intraface_Auth_PrivateKeyLogin(MDB2::singleton(DB_DSN), $credentials['session_id'], $credentials['private_key']);
		$weblogin = $auth_adapter->auth();

		if (!$weblogin) {
		    require_once 'XML/RPC2/Exception.php';
            throw new XML_RPC2_FaultException('Access to the intranet denied. The private key is probably wrong.', -5);
		}

        $this->kernel = new Intraface_Kernel();
        $this->kernel->intranet = new Intraface_Intranet($weblogin->getActiveIntranetId());
        $this->kernel->setting = new Intraface_Setting($this->kernel->intranet->get('id'));

        Intraface_Doctrine_Intranet::singleton($this->kernel->intranet->getId());

        $debtor_module = $this->kernel->module('debtor');
    }

    /**
     * Get array with debtor information
     *
     * @param struct $credentials Provided by intraface
     * @param integer $debtor_id  Debtor id
     *
     * @return array
     */
    public function getDebtor($credentials, $debtor_id)
    {
        if (is_object($return = $this->checkCredentials($credentials))) {
            return $return;
        }

        $debtor = Debtor::factory($this->kernel, (int)$debtor_id);
        if (!$debtor->get('id') > 0) {
            return 0;
        }
        $debtor_info = array_merge($debtor->get());

        if (!$debtor_info) {
            return array();
        }

        return $this->prepareResponseData($debtor_info);
    }

    /**
     * Gets list with debtors
     *
     * @param struct $credentials Provided by intraface
     * @param string $type        Which type of list (quotation, order, invoice)
     * @param integer $contact_id Contact id
     *
     * @return array
     */
    public function getDebtorList($credentials, $type, $contact_id)
    {
        if (is_object($return = $this->checkCredentials($credentials))) {
            return $return;
        }

        $debtor = Debtor::factory($this->kernel, 0, $type);
        $debtor->getDBQuery()->setFilter('contact_id', $contact_id);
        $debtor->getDBQuery()->setFilter('status', '-1');
        $debtors = $debtor->getList();

        return $this->prepareResponseData($debtors);
    }

    /**
     * Alpha - will probably be deprecated shortly
     *
     * @param struct $credentials Credentials provided by intraface
     * @param integer $debtor_id  Id of the debtor
     *
     * @return string
     */
    public function getDebtorPdf($credentials, $debtor_id)
    {
        if (is_object($return = $this->checkCredentials($credentials))) {
            return $return;
        }

        $this->kernel->translation = new Intraface_XMLRPC_Debtor_Server_Translation;

        $debtor = Debtor::factory($this->kernel, (int)$debtor_id);
        if (!$debtor->get('id') > 0) {
            return '';
        }

        if (($debtor->get("type") == "order" || $debtor->get("type") == "invoice") && $this->kernel->intranet->hasModuleAccess('onlinepayment')) {
            $this->kernel->useModule('onlinepayment');
            $onlinepayment = OnlinePayment::factory($this->kernel);
        } else {
            $onlinepayment = NULL;
        }

        if ($this->kernel->intranet->get("pdf_header_file_id") != 0) {
            $this->kernel->useModule('filemanager');
            $filehandler = new FileHandler($this->kernel, $this->kernel->intranet->get("pdf_header_file_id"));
        } else {
            $filehandler = NULL;
        }

        $report = new Intraface_modules_debtor_Visitor_Pdf($this->kernel->getTranslation('debtor'), $filehandler);
        $report->visit($debtor, $onlinepayment);

        $encoded = XML_RPC2_Value::createFromNative($report->output('string'), 'base64');
        return $encoded;

    }
}
