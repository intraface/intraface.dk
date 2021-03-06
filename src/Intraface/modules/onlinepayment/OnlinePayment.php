<?php
/**
 * Online payment
 *
 * Online payment is able to add payments to a debtor.
 *
 * @package Intraface_OnlinePayment
 */
class OnlinePayment extends Intraface_Standard
{
    public $id;
    public $kernel;
    protected $dbquery;

    protected $currency;

    /**
     * Standard transactions statuses from providers. Based on QuickPay
     * @var array
     */
    public $transaction_status_types = array(
        '' => 'Ingen kontakt til udbyder - mangler $eval',
        '000' => '', // Betalingsoplysninger godkendt
        '001' => 'Afvist af PBS',
        '002' => 'Kommunikationsfejl',
        '003' => 'Kort udløbet',
        '004' => 'Status er forkert (Ikke autoriseret)',
        '005' => 'Autorisation er forældet',
        '006' => 'Fejl hos PBS',
        '007' => 'Fejl hos udbyder',
        '008' => 'Fejl i parameter sendt til udbyder'
    );

    public $transaction_status_authorized = "000";

    public function __construct($kernel, $id = 0)
    {
        $this->kernel = $kernel;
        $this->id = $id;
        $this->error = new Intraface_Error;

        // @todo is this the proper place to get the provider key?
        $this->provider_key = $kernel->getSetting()->get('intranet', 'onlinepayment.provider_key');
        $this->dbquery = $this->getDBQuery();

        if ($this->id > 0) {
            $this->load();
        } else {
            $this->value['id'] = 0;
        }
    }

    public static function factory($kernel, $type = 'settings', $value = 0)
    {
        $gateway = new Intraface_modules_onlinepayment_OnlinePaymentGateway($kernel);

        switch ($type) {
            case 'settings':
                return $gateway->findBySettings();
                break;
            case 'id':
                return $gateway->findById($value);
            case 'provider':
                return $gateway->findByProvider($value);
            case 'transactionnumber':
                return $gateway->findByTransactionNumber($value);
            default:
                throw new Exception('Ikke gyldig type i Onlinebetaling');
                break;
        }
    }

    function load()
    {
        $db = new DB_Sql;
        $db->query("SELECT id, date_created, date_authorized, date_captured, date_reversed, belong_to_key, belong_to_id, text, status_key, amount, original_amount, transaction_number, transaction_status, pbs_status, currency_id,
                captured_in_currency_payment_exchange_rate_id,
                DATE_FORMAT(date_created, '%d-%m-%Y') AS dk_date_created,
                DATE_FORMAT(date_authorized, '%d-%m-%Y') AS dk_date_authorized,
                DATE_FORMAT(date_captured, '%d-%m-%Y') AS dk_date_captured,
                DATE_FORMAT(date_reversed, '%d-%m-%Y') AS dk_date_reversed
            FROM onlinepayment WHERE intranet_id = ".$this->kernel->intranet->get('id')." AND id = ".$this->id);
        if ($db->nextRecord()) {
            $this->value['id'] = $db->f('id');
            $this->value['dk_date_created'] = $db->f('dk_date_created');
            $this->value['date_created'] = $db->f('date_created');

            $this->value['dk_date_authorized'] = $db->f('dk_date_authorized');
            $this->value['date_authorized'] = $db->f('date_authorized');

            $this->value['dk_date_captured'] = $db->f('dk_date_captured');
            $this->value['date_captured'] = $db->f('date_captured');

            $this->value['dk_date_reversed'] = $db->f('dk_date_reversed');
            $this->value['date_reversed'] = $db->f('date_reversed');

            $this->value['belong_to_key'] = $db->f('belong_to_key');
            $belong_to_types = $this->getBelongToTypes();
            $this->value['belong_to'] = $belong_to_types[$db->f('belong_to_key')];
            $this->value['belong_to_id'] = $db->f('belong_to_id');
            $this->value['text'] = $db->f('text');
            $this->value['status_key'] = $db->f('status_key');
            $status_types = OnlinePayment::getStatusTypes();
            $this->value['status'] = $status_types[$db->f('status_key')];
            $this->value['amount'] = $db->f('amount');
            $this->value['dk_amount'] = number_format($db->f('amount'), 2, ",", ".");
            $this->value['currency_id'] = $db->f('currency_id');
            $this->value['captured_in_currency_payment_exchange_rate_id'] = $db->f('captured_in_currency_payment_exchange_rate_id');

            $this->value['original_amount'] = $db->f('original_amount');
            $this->value['dk_original_amount'] = number_format($db->f('original_amount'), 2, ",", ".");

            $this->value['transaction_number'] = $db->f('transaction_number');
            $this->value['transaction_status'] = $db->f('transaction_status');
            $this->value['pbs_status'] = $db->f('pbs_status');
            $this->value['transaction_status_translated'] = $this->transaction_status_types[$db->f('transaction_status')];
            if ($db->f('transaction_status') != $this->transaction_status_authorized) {
                $this->value['user_transaction_status_translated'] = $this->transaction_status_types[$db->f('transaction_status')];
            } else {
                $this->value['user_transaction_status_translated'] = "";
            }
            return $this->id;
        } else {
            $this->id = 0;
            $this->value['id'] = 0;
            return 0;
        }
    }

    /**
     * Saves online payment through the xmlrpc webservice
     *
     * @param array $input (belong_to, belong_to_id, transaction_number, transaction_status, amount)
     *
     * @return integer
      */
    public function save($input)
    {
        $input = safeToDb($input);

        if (!isset($input['belong_to'])) {
            $input['belong_to'] = 0;
        }

        if (!isset($input['belong_to_id'])) {
            $input['belong_to_id'] = 0;
        }

        if (!isset($input['transaction_number'])) {
            $input['transaction_number'] = 0;
        }

        if (!isset($input['transaction_status'])) {
            $input['transaction_status'] = '';
        }

        if (!isset($input['pbs_status'])) {
            $input['pbs_status'] = '';
        }

        if (!isset($input['text'])) {
            $input['text'] = '';
        }

        if ($input['transaction_status'] == $this->transaction_status_authorized) {
             $status_key = 2;
        } else {
            $status_key = 1;
        }

        if (!isset($input['amount'])) {
            $input['amount'] = 0;
        }

        $currency_id = 0;
        if (isset($input['currency']) && is_object($input['currency'])) {
            $currency_id = $input['currency']->getId();
        }

        $validator = new Intraface_Validator($this->error);

        $belong_to_key = array_search($input['belong_to'], $this->getBelongToTypes());
        if ($input['belong_to'] == '' || $belong_to_key === false) {
            $this->error->set("Ugyldig belong_to");
        }

        $validator->isNumeric($input['belong_to_id'], 'belong_to_id er ikke et tal');
        $validator->isNumeric($input['transaction_number'], 'transaction_number er ikke gyldig');

        $validator->isString($input['transaction_status'], 'transaction_status er ikke udfyldt');

        if (!isset($this->transaction_status_types[$input['transaction_status']])) {
            $this->error->set("transaction_status '".$input['transaction_status']."' er ikke en gyldig status");
        }
        $validator->isString($input['pbs_status'], 'pbs status er ikke udfyldt', '', 'allow_empty');

        $validator->isString($input['text'], 'text er ikke en gyldig streng', '', 'allow_empty');

        if ($validator->isDouble($input['amount'], 'amount er ikke et gyldigt beløb')) {
            $amount = new Intraface_Amount($input['amount']);
            if ($amount->convert2db()) {
                $input['amount'] = $amount->get();
            } else {
                $this->error->set("Kunne ikke konvertere amount til databasen!");
            }
        }

        if ($this->error->isError()) {
            return 0;
        }

        $sql = "date_changed = NOW(),
            status_key = ".$status_key.",
            belong_to_key = ".$belong_to_key.",
            belong_to_id = ".$input['belong_to_id'].",
            text = \"".$input['text']."\",
            transaction_number = ".$input['transaction_number'].",
            transaction_status = \"".$input['transaction_status']."\",
            pbs_status = \"".$input['pbs_status']."\",
            amount = ".$input['amount'].",
            provider_key = ".$this->provider_key.",
            original_amount = ".$input['amount'].",
            currency_id = ".$currency_id;

        $db = new DB_Sql;

        if ($this->id > 0) {
            $db->query("UPDATE onlinepayment SET ".$sql." WHERE intranet_id = ".$this->kernel->intranet->get('id')." AND id = ".$this->id);
        } else {
            $db->query("INSERT INTO onlinepayment SET ".$sql.",
                intranet_id = ".$this->kernel->intranet->get('id').",
                date_created = NOW()");
            $this->id = $db->insertedId();
        }
        $this->load();

        return $this->id;
    }

    /**
     * Creates an onlinepayment to be processed
     *
     * @return integer payment_id
     */
    public function create()
    {
        $provider_key = $this->kernel->getSetting()->get('intranet', 'onlinepayment.provider_key');
        $db = new DB_Sql;

        $db->query("INSERT INTO onlinepayment SET
            status_key = 1,
            intranet_id = ".$this->kernel->intranet->get('id').",
            date_created = NOW(),
            provider_key = ".$provider_key);
        return $db->insertedId();
    }

    /**
     * Updates payment from intraface
     *
     * @param array $input
     *
     * return integer
     */
    function update($input)
    {
        if ($this->id == 0) {
            throw new Exception("OnlinePayment->update kan kun køres på en allerede oprettet betaling");
        }

        if ($this->getStatus() != 'authorized') {
            throw new Exception("OnlinePayment->update kan kun køres på betaling der er authorized");
        }

        $input = safeToDb($input);

        $validator = new Intraface_Validator($this->error);

        if ($validator->isDouble($input['dk_amount'], 'Beløb er ikke et gyldigt beløb', 'greater_than_zero')) {
            $amount = new Intraface_Amount($input['dk_amount']);
            if ($amount->convert2db()) {
                $input['amount'] = $amount->get();
            } else {
                $this->error->set("Kunne ikke konvertere amount til databasen!");
            }
        }

        if ($input['amount'] > $this->get('original_amount')) {
            $this->error->set("Du kan ikke sætte beløbet højere end hvad kunden har godkendt: ".$this->get('dk_original_amount'));
        }

        if ($this->error->isError()) {
            return 0;
        }

        $db = new DB_Sql;
        $db->query("UPDATE onlinepayment SET amount = ".$input['amount'].", date_changed = NOW() WHERE intranet_id = ".$this->kernel->intranet->get('id')." AND id = ".$this->id);
        return $this->id;
    }

    function changeBelongTo($belong_to, $belong_to_id)
    {
        if ($this->id == 0) {
            throw new Exception("OnlinePayment->setBelongTo kan kun ændre eksisterende betalinger");
        }

        $belong_to = safeToDb($belong_to);

        $belong_to_key = array_search($belong_to, $this->getBelongToTypes());
        if ($belong_to == '' || $belong_to_key === false) {
            throw new Exception("Ugyldig belong_to i OnlinePayment->changeBelongTo()");
        }

        if (!is_int($belong_to_id)) {
            throw new Exception("Belong_to_id er ikke et tal i OnlinePayment->changeBelongTo()");
        }

        $db = new DB_Sql;
        $db->query("UPDATE onlinepayment SET belong_to_key = ".$belong_to_key.", belong_to_id = ".$belong_to_id." WHERE intranet_id = ".$this->kernel->intranet->get('id')." AND id = ".$this->id);
        return $this->id;
    }

    function setStatus($status)
    {
        if ($this->id == 0) {
            throw new Exception("OnlinePayment->setStatus kan kun ændre eksisterende betalinger");
        }
        $status = safeToDb($status);


        $status_key = array_search($status, OnlinePayment::getStatusTypes());
        if ($status == "" || $status_key === false) {
            throw new Exception("Ugyldig status i OnlinePayment->setStatus()");
        }

        if ($status_key <= $this->get('status_key')) {
            throw new Exception("Kan ikke skifte til lavere eller samme status i OnlinePayment->setStatus()");
        }

        switch ($status) {
            case "authorized":
                $date_field = "date_authorized";
                break;
            case "captured":
                $date_field = "date_captured";
                break;
            case "reversed":
                $date_field = "date_reversed";
                break;
            case "cancelled":
                $date_field = "date_cancelled";
                break;
        }

        $db = new DB_Sql;

        $db->query("UPDATE onlinepayment SET status_key = ".$status_key.", ".$date_field." = NOW() WHERE intranet_id = ".$this->kernel->intranet->get('id')." AND id = ".$this->id);

        $this->value['status_key'] = $status_key;

        return true;
    }

    /**
     * Adds onlinepayment to invoice
     */
    function addAsPayment()
    {
        if ($this->get('status') != 'authorized') {
            $this->error->set("Der kan kun udføres handlinger på betalinger der er godkendt");
            return false;
        }

        if ($this->get('belong_to') != 'invoice') {
            $this->error->set("Der kan kun udføres handlinger på betalinger der er tilknyttet en faktura");
            return false;
        }

        if (!$this->kernel->intranet->hasModuleAccess('invoice')) {
            return false;
        }

        $invoice_module = $this->kernel->getModule('debtor', true); // true: tjekker kun intranet adgang

        $invoice = Debtor::factory($this->kernel, (int)$this->get('belong_to_id'));

        if ($invoice->get('id') == 0) {
            $this->error->set("Ugyldig faktura");
            return false;
        }

        $payment = new Payment($invoice);

        $input = array(
            "payment_date" => date("d-m-Y"),
            "amount" => $this->getAmountInSystemCurrency()->getAsLocal('da_dk', 2),
            "description" => "Transaction ".$this->get('transaction_number'),
            "type" => 2 // credit card
        );

        if ($payment->update($input)) {
            $this->value['create_payment_id'] = $payment->get('id');
            if ($this->getCurrency()) {
                $db = new DB_Sql;
                $db->query("UPDATE onlinepayment SET captured_in_currency_payment_exchange_rate_id = ".$this->getCurrency()->getPaymentExchangeRate()->getId()." WHERE intranet_id = ".$this->kernel->intranet->get('id')." AND id = ".$this->id);
            }
            return true;
        } else {
            $this->error->merge($payment->error->getMessage());
            return false;
        }
    }

    /**
     * Returns the possible actions to perform on an onlinepayment.
     * These are defined individually to all providers. The actual action is executed in OnlinePayment->transactionAction()
     *
     * Nb. the action 'capture' is not shown in debtor (view.php) before it is an sent invoice.
     *
     * @todo better description of this, what is it used for. I think that the label
     *       has to go by the way.
     *
     * @return array    with actions to perform on onlinepayment.
     */
    function getTransactionActions()
    {
        return array(
            0 => array(
                'action' => 'capture',
                'label' => 'Hæv')
        );
    }

    function transactionAction($action)
    {
        return false;
    }

    function getList()
    {
        if ($this->getDBQuery()->getFilter('belong_to') != '') {
            if ($this->getDBQuery()->getFilter('belong_to_id') == 0) {
                throw new Exception("belong_to_id er nul i OnlinePayment->getList()");
            }
            $belong_to_key = array_search($this->dbquery->getFilter('belong_to'), $this->getBelongToTypes());
            if ($this->getDBQuery()->getFilter('belong_to') == '' || $belong_to_key === false) {
                throw new Exception("belong_to_key er ikke gyldig i OnlinePayment->getList()");
            }
            $this->getDBQuery()->setCondition("belong_to_key = ".$belong_to_key." AND belong_to_id = ".$this->dbquery->getFilter('belong_to_id'));
        }

        if ($this->getDBQuery()->getFilter('status') > 0) {
            $this->getDBQuery()->setCondition("status_key = ".intval($this->getDBQuery()->getFilter('status')));
        }

        if ($this->getDBQuery()->getFilter('text') != "") {
            $this->getDBQuery()->setCondition("transaction_number LIKE \"%".$this->getDBQuery()->getFilter('text')."%\" OR text LIKE \"%".$this->dbquery->getFilter('text')."%\"");
        }

        if ($this->getDBQuery()->checkFilter("from_date")) {
            $date = new Intraface_Date($this->getDBQuery()->getFilter("from_date"));
            if ($date->convert2db()) {
                $this->getDBQuery()->setCondition("date_created >= \"".$date->get()."\"");
            } else {
                $this->error->set("Fra dato er ikke gyldig");
            }
        }

        // Poster med fakturadato før slutdato.
        if ($this->getDBQuery()->checkFilter("to_date")) {
            $date = new Intraface_Date($this->getDBQuery()->getFilter("to_date"));
            if ($date->convert2db()) {
                $this->getDBQuery()->setCondition("date_created <= \"".$date->get()."\"");
            } else {
                $this->error->set("Til dato er ikke gyldig");
            }
        }

        $doctrine = Doctrine_Manager::connection(DB_DSN);
        $doctrine->setCharset('utf8');

        // @todo this does not work
        $currency_gateway = new Intraface_modules_currency_Currency_Gateway($doctrine);

        $this->getDBQuery()->setSorting("date_created DESC");
        $db = $this->getDBQuery()->getRecordset("id, date_created, belong_to_key, belong_to_id, text, status_key, amount, provider_key, transaction_number, transaction_status, pbs_status, currency_id, DATE_FORMAT(date_created, '%d-%m-%Y %H:%i') AS dk_date_created", "", false);
        $i = 0;
        $list = array();

        while ($db->nextRecord()) {
            $list[$i]['id'] = $db->f('id');
            $list[$i]['dk_date_created'] = $db->f('dk_date_created');
            $list[$i]['date_created'] = $db->f('date_created');
            $list[$i]['belong_to_key'] = $db->f('belong_to_key');
            $belong_to_types = $this->getBelongToTypes();
            $list[$i]['belong_to'] = $belong_to_types[$db->f('belong_to_key')];
            $list[$i]['belong_to_id'] = $db->f('belong_to_id');
            $list[$i]['text'] = $db->f('text');
            $list[$i]['status_key'] = $db->f('status_key');
            $status_types = OnlinePayment::getStatusTypes();
            $list[$i]['status'] = $status_types[$db->f('status_key')];
            $list[$i]['amount'] = $db->f('amount');
            $list[$i]['provider_key'] = $db->f('provider_key');
            $list[$i]['dk_amount'] = number_format($db->f('amount'), 2, ",", ".");
            $list[$i]['transaction_number'] = $db->f('transaction_number');
            $list[$i]['pbs_status'] = $db->f('pbs_status');
            $list[$i]['transaction_status'] = $db->f('transaction_status');
            if (array_key_exists($list[$i]['transaction_status'], $this->transaction_status_types)) {
                $list[$i]['transaction_status_translated'] = $this->transaction_status_types[$db->f('transaction_status')];
            } else {
                $list[$i]['transaction_status_translated'] = 'invalid status';
            }

            if ($db->f('currency_id') != 0) {
                $list[$i]['currency'] = $currency_gateway->findById($db->f('currency_id'));
            } else {
                $list[$i]['currency'] = false;
            }

            // @todo What is this for?
            if (array_key_exists($list[$i]['transaction_status'], $this->transaction_status_types) && $db->f('transaction_status') != $this->transaction_status_authorized) {
                $list[$i]['user_transaction_status_translated'] = $this->transaction_status_types[$db->f('transaction_status')];
            } else {
                $list[$i]['user_transaction_status_translated'] = "";
            }

            $i++;
        }
        return $list;
    }

    /**
     * Returns the currency if set, otherwise false
     */
    public function getCurrency()
    {
        if ($this->get('currency_id') == 0) {
            return false;
        }

        if (!$this->currency) {
            $doctrine = Doctrine_Manager::connection(DB_DSN);
            $gateway = new Intraface_modules_currency_Currency_Gateway($doctrine);
            $this->currency = $gateway->findById($this->get('currency_id'));
        }

        return $this->currency;
    }

    /**
     * Returns the amount in the given currency
     */
    public function getAmount()
    {
        return new Ilib_Variable_Float($this->get('amount'));
    }

    /**
     * Returns an approximate amount in the
     */
    public function getAmountInSystemCurrency()
    {
        if ($this->getCurrency()) {
            if ($this->get('status') == 'captured') {
                return new Ilib_Variable_Float(
                    $this->getCurrency()
                    ->getPaymentExchangeRate(
                        $this->get('captured_in_currency_payment_exchange_rate_id')
                    )
                    ->convertAmountFromCurrency($this->getAmount())->getAsIso(2)
                );
            } else {
                return new Ilib_Variable_Float($this->getCurrency()->getPaymentExchangeRate()->convertAmountFromCurrency($this->getAmount())->getAsIso(2));
            }
        }
        return $this->getAmount();
    }


    /**
     * returns the possible status types
     *
     * @return array with status types
     */
    static function getStatusTypes()
    {
        return array(
            0 => '',
            1 => 'created',
            2 => 'authorized',
            3 => 'captured',
            4 => 'reversed',
            5 => 'cancelled');
    }

    /**
     * returns possible belong to types
     *
     * @return array with belong to types
     */
    private function getBelongToTypes()
    {
        return array(
            0 => '',
            1 => 'order',
            2 => 'invoice');
    }

    /**
     * returns the implemented providers
     *
     * @return array with providers
     */
    static function getImplementedProviders()
    {
        return array(
            0 => '_invalid_',
            1 => 'default', // reserved for custom provider, where everything runs outside the system
            2 => 'quickpay',
            3 => 'dandomain'
        );
    }

    function getDBQuery()
    {
        if ($this->dbquery) {
            return $this->dbquery;
        }

        $this->dbquery = new Intraface_DBQuery($this->kernel, "onlinepayment", "intranet_id = ".$this->kernel->intranet->get("id"));
        $this->dbquery->useErrorObject($this->error);

        return $this->dbquery;
    }

    function isFilledIn()
    {
        return true; // No settings for the online payment
    }

    function isSettingsSet()
    {
        return true;
    }

    function isProviderSet()
    {
        return $this->kernel->getSetting()->get('intranet', 'onlinepayment.provider_key');
    }

    function setProvider($input)
    {
        // @todo check whether all payments has been dealt with when changing provider
        $this->kernel->getSetting()->set('intranet', 'onlinepayment.provider_key', $input['provider_key']);
        return true;
    }

    function getProvider()
    {
        return array('provider_key' => $this->kernel->getSetting()->get('intranet', 'onlinepayment.provider_key'));
    }

    function getStatus()
    {
        $status =  $this->getStatusTypes();
        return $status[$this->value['status_key']];
    }
}
