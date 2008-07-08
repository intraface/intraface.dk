<?php
/**
 * @package Intraface_Invoice
 */
class Reminder extends Intraface_Standard
{
    public $id;
    public $kernel;
    public $value;
    public $contact;
    private $db;
    public $item;
    public $error;
    public $dbquery;

    function __construct($kernel, $id = 0)
    {
        $this->id     = intval($id);
        $this->kernel = $kernel;

        $this->db     = new DB_Sql;
        $this->error  = new Intraface_Error;

        if ($this->id) {
            $this->load();
        }
    }

    function getDBQuery()
    {
        if ($this->dbquery) {
            return $this->dbquery;
        }
        $this->dbquery = new Intraface_DBQuery($this->kernel, "invoice_reminder", "intranet_id = ".$this->kernel->intranet->get("id")." AND active = 1");
        $this->dbquery->useErrorObject($this->error);
        return $this->dbquery;
        
    }

    function load()
    {
        $this->db->query("SELECT *,
                DATE_FORMAT(this_date, '%d-%m-%Y') AS dk_this_date,
                DATE_FORMAT(due_date, '%d-%m-%Y') AS dk_due_date,
                DATE_FORMAT(date_sent, '%d-%m-%Y') AS dk_date_sent,
                DATE_FORMAT(date_executed, '%d-%m-%Y') AS dk_date_executed,
                DATE_FORMAT(date_cancelled, '%d-%m-%Y') AS dk_date_cancelled
            FROM invoice_reminder WHERE id = ".$this->id." AND intranet_id = ".$this->kernel->intranet->get("id")."");
        if (!$this->db->nextRecord()) {
            $this->id = 0;
            $this->value['id'] = 0;
            return false;
        }

        $this->value["id"] = $this->db->f("id");
        $this->value["invoice_id"] = $this->db->f("invoice_id");
        $this->value["intranet_id"] = $this->db->f("intranet_id");
        $this->value["intranet_address_id"] = $this->db->f("intranet_address_id");
        $this->value["contact_id"] = $this->db->f("contact_id");
        $this->value["contact_address_id"] = $this->db->f("contact_address_id");
        $this->value["contact_person_id"] = $this->db->f("contact_person_id");
        $this->value["user_id"] = $this->db->f("user_id");
        $status_types = $this->getStatusTypes();
        $this->value["status"] = $status_types[$this->db->f("status")]; // skal laves om til db->f('status_key')
        $this->value["status_id"] = $this->db->f("status"); // skal slettes i næste version
        $this->value["status_key"] = $this->db->f("status");
        $this->value["this_date"] = $this->db->f("this_date");
        $this->value["dk_this_date"] = $this->db->f("dk_this_date");
        $this->value["due_date"] = $this->db->f("due_date");
        $this->value["dk_due_date"] = $this->db->f("dk_due_date");
        $this->value["dk_date_sent"] = $this->db->f("dk_date_sent");
        $this->value["dk_date_executed"] = $this->db->f("dk_date_executed");
        $this->value["date_stated"] = $this->db->f("date_stated");
        $this->value["voucher_id"] = $this->db->f("voucher_id");
        $this->value["description"] = $this->db->f("description");
        $this->value["number"] = $this->db->f("number");
        $this->value["payment_method_key"] = $this->db->f("payment_method");
        $payment_methods = $this->getPaymentMethods();
        $this->value["payment_method"] = $payment_methods[$this->db->f("payment_method")];
        $this->value["reminder_fee"] = $this->db->f("reminder_fee");
        // Denne skal laves, så den udregner hele værdien af hele rykkeren
        $this->value["total"] = $this->db->f("reminder_fee");
        $this->value["text"] = $this->db->f("text");
        $this->value["girocode"] = $this->db->f("girocode");
        $this->value["send_as"] = $this->db->f("send_as");

        $this->contact = new Contact($this->kernel, $this->db->f("contact_id"), $this->db->f("contact_address_id"));
        if ($this->contact->get("type") == "corporation" && $this->db->f("contact_person_id") != 0) {
            $this->contact_person = new ContactPerson($this->contact, $this->db->f("contact_person_id"));
        }


        if ($this->get("status") == "executed" || $this->get("status") == "cancelled") {
            $this->value["locked"] = true;
        } else {
            $this->value["locked"] = false;
        }

        $this->value['payment_total'] = 0;
        foreach ($this->getDebtorAccount()->getList() AS $payment) {
            $this->value['payment_total'] += $payment["amount"];
        }
        $this->value["arrears"] = $this->value['total'] - $this->value['payment_total'];

        return true;

    }

    function loadItem($id = 0)
    {
        require_once 'Intraface/modules/invoice/ReminderItem.php';
        $this->item = new ReminderItem($this, (int)$id);
    }

    function getMaxNumber()
    {
        $this->db->query("SELECT MAX(number) AS max_number FROM invoice_reminder WHERE intranet_id = ".$this->kernel->intranet->get("id"));
        $this->db->nextRecord(); // Hvis der ikke er nogle poster er dette bare den første
        $number = $this->db->f("max_number") + 1;
        return $number;
    }

    function isNumberFree($number)
    {
        $sql = "SELECT id FROM invoice_reminder WHERE number = ".intval($number)." AND id != ".$this->id . " AND intranet_id = " . $this->kernel->intranet->get('id');
        $this->db->query($sql);
        if ($this->db->nextRecord()) {
            return false;
        } else {
            return true;
        }
    }

    function save($input)
    {
        if ($this->get("locked") == 1) {
            return(false);
        }

        if (!isset($input['payment_method_key'])) {
            $input['payment_method_key'] = 0;
        }

        if (!is_array($input)) {
            trigger_error("Input er ikke et array", E_USER_ERROR);
        }

        $input = safeToDb($input);

        $validator = new Intraface_Validator($this->error);

        if (!isset($input['number'])) $input['number'] = 0;
        if ($validator->isNumeric($input["number"], "Rykkernummer skal være et tal større end nul", "greater_than_zero")) {
            if (!$this->isNumberFree($input["number"])) {
                $this->error->set("Rykkernummer er allerede brugt");
            }
        }

        if (!isset($input['contact_id'])) $input['contact_id'] = 0;
        if (!isset($input["contact_person_id"])) $input["contact_person_id"] = 0;
        if ($validator->isNumeric($input["contact_id"], "Du skal angive en kunde", "greater_than_zero")) {
            $contact = new Contact($this->kernel, (int)$input["contact_id"]);
            if (is_object($contact->address)) {
                $contact_id = $contact->get("id");
                $contact_address_id = $contact->address->get("address_id");
            } else {
                $this->error->set("Ugyldig kunde");
            }

            if ($contact->get("type") == "corporation") {
                $validator->isNumeric($input["contact_person_id"], "Der er ikke angivet en kontaktperson");
            }
        }

        // $validator->isString($input["attention_to"], "Fejl i att.", "", "allow_empty");
        if (!isset($input['description'])) $input['description'] = '';
        $validator->isString($input["description"], "Fejl i beskrivelsen", "", "allow_empty");

        if (!isset($input['this_date'])) $input['this_date'] = '';
        if ($validator->isDate($input["this_date"], "Ugyldig dato", "allow_no_year")) {
            $this_date = new Intraface_Date($input["this_date"]);
            $this_date->convert2db();
        }

        if (!isset($input['due_date'])) $input['due_date'] = '';
        if ($validator->isDate($input["due_date"], "Ugyldig forfaldsdato", "allow_no_year")) {
            $due_date = new Intraface_Date($input["due_date"]);
            $due_date->convert2db();
        }

        if (!isset($input['reminder_fee'])) $input['reminder_fee'] = 0;
        $validator->isNumeric($input["reminder_fee"], "Rykkerbebyr skal være et tal");
        if (!isset($input['text'])) $input['text'] = '';
        $validator->isString($input["text"], "Fejl i teksten", "<b><i>", "allow_empty");
        if (!isset($input['send_as'])) $input['send_as'] = '';
        $validator->isString($input["send_as"], "Ugyldig måde at sende rykkeren på");

        if (!isset($input['payment_method_key'])) $input['payment_method_key'] = 0;
        $validator->isNumeric($input["payment_method_key"], "Du skal angive en betalingsmetode");
        if (!isset($input['girocode'])) $input['girocode'] = '';
        if ($input["payment_method_key"] == 3) {
            $validator->isString($input["girocode"], "Du skal udfylde girokode");
        } else {
            $validator->isString($input["girocode"], "Ugyldig girokode", "", "allow_empty");
        }

        if (!isset($input['checked_invoice'])) $input['checked_invoice'] = array();
        if (!is_array($input["checked_invoice"]) || count($input["checked_invoice"]) == 0) {
            $this->error->set("Der er ikke valgt nogle fakturaer til rykkeren");
        }

        if ($this->error->isError()) {
            return(false);
        }

        $sql = "intranet_address_id = ".$this->kernel->intranet->address->get("address_id").",
            number = ".$input["number"].",
            contact_id = ".$contact_id.",
            contact_address_id = ".$contact_address_id.",
            contact_person_id = ".$input['contact_person_id'].",
            description = \"".$input["description"]."\",
            this_date = \"".$this_date->get()."\",
           due_date = \"".$due_date->get()."\",
            reminder_fee = ".$input["reminder_fee"].",
            text = \"".$input["text"]."\",
            send_as = \"".$input["send_as"]."\",
            payment_method = ".$input["payment_method_key"].",
            girocode = \"".$input["girocode"]."\",
            date_changed = NOW()";

        // attention_to = \"".$input["attention_to"]."\",
        if ($this->id) {
            $this->db->query("UPDATE invoice_reminder SET ".$sql." WHERE id = ".$this->id);
            $this->load();
        } else {
            $this->db->query("INSERT INTO invoice_reminder SET ".$sql.", intranet_id = ".$this->kernel->intranet->get("id").", date_created = NOW(), user_id = ".$this->kernel->user->get("id"));
            $this->id = $this->db->insertedId();
            $this->load();
        }

        $this->loadItem();
        $this->item->clear();

        if (isset($input["checked_invoice"]) && is_array($input["checked_invoice"])) {
            foreach ($input["checked_invoice"] AS $invoice_id) {
                $this->loadItem();
                $this->item->save(array("invoice_id" => $invoice_id));
            }
        }

        if (isset($input["checked_reminder"]) && is_array($input["checked_reminder"])) {
            foreach ($input["checked_reminder"] AS $reminder_id) {
                $this->loadItem();
                $this->item->save(array("reminder_id" => $reminder_id));
            }
        }

        return true;
    }

    function delete()
    {
        $this->db->query("UPDATE invoice_reminder SET active = 0 WHERE id = ".$this->id." AND intranet_id = ".$this->kernel->intranet->get("id"));
        $this->id = 0;
        $this->load();
        return true;
    }


    /*
    function setLocked() {
        if ($this->get("locked") == 0) {

            $this->db->query("UPDATE invoice_reminder SET locked = 1 WHERE id = ".$this->id);
            $this->load();
        }
    }
    */

    /**
     * Sætter status for rykkeren
     *
     * @return true / false
     */
    function setStatus($status) {

        if (is_string($status)) {
            $status_id = array_search($status, $this->getStatusTypes());
            if ($status_id === false) {
                trigger_error("Reminder->setStatus(): Ugyldig status (streng)", FATAL);
            }
        } else {
            $status_id = intval($status);
            $status_types = $this->getStatusTypes();
            if (isset($status_types[$status_id])) {
                $status = $status_types[$status];
            } else {
                trigger_error("Reminder->setStatus(): Ugyldig status (integer)", E_USER_ERROR);
            }
        }

        if ($status_id <= $this->get("status_id")) {
            $this->error->set('Du kan ikke sætte status til samme som/lavere end den er i forvejen');
            trigger_error("Tried to set status the same or lower than it was before. Can be because of reload. In Reminder->setStatus", E_USER_NOTICE);
            return false;

        }

        switch($status) {
            case "sent":
                $sql = "date_sent = NOW()";
                break;

            case "executed":
                $sql = "date_executed = NOW()";
                break;

            case "cancelled":
                $sql = "date_cancelled = NOW()";
                break;

            default:
                trigger_error("Dette kan ikke lade sig gøre! Reminder->setStatus()", FATAL);
        }

        $db = new Db_Sql;
        $db->query("UPDATE invoice_reminder SET status = ".$status_id.", ".$sql."  WHERE id = " . $this->id . " AND intranet_id = " . $this->kernel->intranet->get('id'));
        $this->load();
        return true;
    }

    function updateStatus()
    {

        if ($this->get("arrears") == 0 && $this->get("status") == "sent") {
            $this->setStatus("executed");
        }
        return true;
    }

    /**
     * returns DebtorAccount object
     *
     * @return object DebtorAccount
     */
    public function getDebtorAccount()
    {
        require_once 'Intraface/modules/invoice/DebtorAccount.php';
        return new DebtorAccount($this);
    }

    /*
    removed 22/1 2008
    function getPayments()
    {

        $this->payment = new Payment($this);
        $payments = $this->payment->getList();

        $payment["payment"] = 0;
        $payment["deprication"] = 0;

        for($i = 0, $max = count($payments); $i < $max; $i++) {
            if ($payments[$i]["type"] == 'depriciation') {
                $payment['depriciation'] += $payments[$i]["amount"];
            } else {
                $payment['payment'] += $payments[$i]["amount"];
            }
        }

        $payment["total"] = $payment["payment"] + $payment["deprication"];
        return $payment;
    }
    */

    function getList()
    {
        $this->dbquery->setSorting("number DESC, this_date DESC");
        $i = 0;

        if ($this->dbquery->checkFilter("contact_id")) {
            $this->dbquery->setCondition("contact_id = ".intval($this->dbquery->getFilter("contact_id")));
        }

        if ($this->dbquery->checkFilter("text")) {
            $this->dbquery->setCondition("(description LIKE \"%".$this->dbquery->getFilter("text")."%\" OR girocode = \"".$this->dbquery->getFilter("text")."\" OR number = \"".$this->dbquery->getFilter("text")."\")");
        }

        if ($this->dbquery->checkFilter("from_date")) {
            $date = new Intraface_Date($this->dbquery->getFilter("from_date"));
            if ($date->convert2db()) {
                $this->dbquery->setCondition("this_date >= \"".$date->get()."\"");
            } else {
                $this->error->set("Fra dato er ikke gyldig");
            }
        }

        // Poster med fakturadato før slutdato.
        if ($this->dbquery->checkFilter("to_date")) {
            $date = new Intraface_Date($this->dbquery->getFilter("to_date"));
            if ($date->convert2db()) {
                $this->dbquery->setCondition("this_date <= \"".$date->get()."\"");
            } else {
                $this->error->set("Til dato er ikke gyldig");
            }
        }

        if ($this->dbquery->checkFilter("status")) {
            if ($this->dbquery->getFilter("status") == "-1") {
                // Behøves ikke, den tager alle.
                // $this->dbquery->setCondition("status >= 0");
            } elseif ($this->dbquery->getFilter("status") == "-2") {
                // Not executed = åbne
                if ($this->dbquery->checkFilter("to_date")) {
                    $date = new Intraface_Date($this->dbquery->getFilter("to_date"));
                    if ($date->convert2db()) {
                        // Poster der er executed eller cancelled efter dato, og sikring at executed stadig er det, da faktura kan sættes tilbage.
                        $this->dbquery->setCondition("(date_executed >= \"".$date->get()."\" AND status = 2) OR (date_cancelled >= \"".$date->get()."\") OR status < 2");
                    }
                } else {
                    // Hvis der ikke er nogen dato så tager vi alle dem som på nuværende tidspunkt har status under
                    $this->dbquery->setCondition("status < 2");
                }

            } else {
                switch($this->dbquery->getFilter("status")) {
                    case "0":
                        $to_date_field = "date_created";
                        break;

                    case "1":
                        $to_date_field = "date_sent";
                        break;

                    case "2":
                        $to_date_field = "date_executed";
                        break;

                    case "3":
                        $to_date_field = "data_caneled";
                        break;
                }

                if ($this->dbquery->checkFilter("to_date")) {
                    $date = new Intraface_Date($this->dbquery->getFilter("to_date"));
                    if ($date->convert2db()) {
                        $this->dbquery->setCondition($to_date_field." <= \"".$date->get()."\"");
                    }
                } else {
                    // tager dem som på nuværende tidspunkt har den angivet status
                    $this->dbquery->setCondition("status = ".intval($this->dbquery->getFilter("status")));
                }
            }
        }

        $this->dbquery->setSorting("number DESC");
        $db = $this->dbquery->getRecordset("id", "", false);

        $list = array();
        while($db->nextRecord()) {
            $reminder = new Reminder($this->kernel, $db->f("id"));
            $list[$i] = $reminder->get();
            if (is_object($reminder->contact->address)) {
                $list[$i]['contact_id'] = $reminder->contact->get('id');
                $list[$i]['name'] = $reminder->contact->address->get('name');
                $list[$i]['address'] = $reminder->contact->address->get('address');
                $list[$i]['postalcode'] = $reminder->contact->address->get('postcode');
                $list[$i]['city'] = $reminder->contact->address->get('city');
            }
            $i++;
        }
        return $list;
    }

    /**
     * Bruges ift. kontakter
     */
    function any($contact_id)
    {
        $contact_id = (int)$contact_id;
        if ($contact_id == 0) {
            return 0;
        }
        $db = new DB_Sql;
        $db->query("SELECT id
            FROM invoice_reminder WHERE intranet_id = ".$this->kernel->intranet->get("id")." AND contact_id=" . $contact_id);
        return $db->numRows();
    }

    function isFilledIn()
    {
        $db = new DB_Sql;
        $db->query("SELECT id FROM invoice_reminder WHERE intranet_id = " . $this->kernel->intranet->get('id'));
        return $db->numRows();
    }

    /**
     * Set the reminder as stated
     *
     * @param integer $voucher_id   The voucher id
     * @param string  $voucher_date Which date is it stated
     *
     * @return boolean
     */
    public function setStated($voucher_id, $voucher_date)
    {
        // FIXME - check on date
        $db = new DB_Sql;
        $db->query("UPDATE invoice_reminder SET date_stated = '" . $voucher_date . "', voucher_id = '".$voucher_id."' WHERE id = " . $this->id . " AND intranet_id = " . $this->kernel->intranet->get('id'));
        return true;
    }

    /**
     * Check whether reminder has been stated
     *
     * @return boolean
     */
    public function isStated()
    {
        if ($this->get("date_stated") > '0000-00-00') {
            return true;
        }
        return false;
    }

    /**
     * returns whether there is something to state on the reminder
     *
     * @return boolean true or false
     */
    public function somethingToState()
    {

        if ($this->get('total') == 0) {
            return false;
        }
        return true;
    }

    /**
     * Returns whether the reminder is ready for state
     *
     * @param object accounting year
     * @return boolean true or false
     */
    function readyForState($year)
    {
        if ($this->isStated()) {
            $this->error->set('reminder is already stated');
            return false;
        }

        if (!$this->somethingToState()) {
            $this->error->set('there is nothing to state on the reminder');
        }

        if ($this->get('status') != 'sent' && $this->get('status') != 'executed') {
            $this->error->set('the reminder should be sent of executed to be stated');
            return false;
        }

        if (!$year->readyForState()) {
            $this->error->set('accounting year is not ready for state');
            return false;
        }

        $debtor_account = new Account($year, $year->getSetting('debtor_account_id'));
        if ($debtor_account->get('id') == 0 || $debtor_account->get('type') != 'balance, asset') {
            $this->error->set('invalid debtor account set in the accounting settings');
        }

        if ($this->error->isError()) {
            return false;
        }
        return true;
    }

    /**
     * State reminder
     *
     * @param object year stating year
     * @param integer voucher_number
     * @param string voucher_date
     * @return boolean true or false
     */
    function state($year, $voucher_number, $voucher_date, $credit_account_number, $translation)
    {
        if (!is_object($year)) {
            trigger_error('First parameter to state needs to be a Year object!', E_USER_ERROR);
            return false;
        }

        if (!is_object($translation)) {
            trigger_error('5th parameter to state needs to be a translation object!', E_USER_ERROR);
            return false;
        }

        $validator = new Intraface_Validator($this->error);
        if ($validator->isDate($voucher_date, "Ugyldig dato")) {
            $this_date = new Intraface_Date($voucher_date);
            $this_date->convert2db();
        }

        $validator->isNumeric($voucher_number, 'invalid voucher number', 'greater_than_zero');
        $validator->isNumeric($credit_account_number, 'invalid account number for stating reminder', 'greater_than_zero');

        if ($this->error->isError()) {
            return false;
        }

        if (!$this->readyForState($year)) {
            $this->error->set('Reminder is not ready for state');
            return false;
        }

        $text = $translation->get('reminder').' #'.$this->get('number');

        require_once 'Intraface/modules/accounting/Voucher.php';
        require_once 'Intraface/modules/accounting/Account.php';
        $voucher = Voucher::factory($year, $voucher_number);
        $voucher->save(array(
            'voucher_number' => $voucher_number,
            'date' => $voucher_date,
            'text' => $text
        ));


        $credit_account = Account::factory($year, $credit_account_number);
        if ($credit_account->get('id') == 0 || $credit_account->get('type') != 'operating') {
            $this->error->set('invalid account for stating reminder');
        }
        $credit_account_number = $credit_account->get('number');

        $debet_account = new Account($year, $year->getSetting('debtor_account_id'));
        $debet_account_number = $debet_account->get('number');

        $voucher = Voucher::factory($year, $voucher_number);
        $amount = $this->get('total');


        $input_values = array(
            'voucher_number' => $voucher_number,
            'date' => $voucher_date,
            'amount' => number_format($amount, 2, ",", "."),
            'debet_account_number' => $debet_account_number,
            'credit_account_number' => $credit_account_number,
            'text' => $text
        );

        if (!$voucher->saveInDaybook($input_values, true)) {
            $this->error->merge($voucher->error->getMessage());
        }

        require_once 'Intraface/modules/accounting/VoucherFile.php';
        $voucher_file = new VoucherFile($voucher);
        if (!$voucher_file->save(array('description' => $text, 'belong_to'=>'reminder','belong_to_id'=>$this->get('id')))) {
            $this->error->merge($voucher_file->error->getMessage());
            $this->error->set('Filen blev ikke overflyttet');
        }

        if ($this->error->isError()) {
            $this->error->set('An error occured while stating the reminder. This can mean that parts of the reminder was not state correct. Please check the voucher.');
            // I am not quite sure if the invoice should be set as stated, but it can give trouble to state it again, if some of it was stated...
            $this->setStated($voucher->get('id'), $this_date->get());
            return false;
        }

        $this->setStated($voucher->get('id'), $this_date->get());
        $this->load();
        return true;

    }

    function pdf($type = 'stream', $filename='')
    {
        if ($this->get('id') == 0) {
            trigger_error('Cannot create pdf from debtor without valid id', E_USER_ERROR);
        }

        $translation = $this->kernel->getTranslation('debtor');

        $filehandler = '';

        if ($this->kernel->intranet->get("pdf_header_file_id") != 0) {
            $filehandler = new FileHandler($this->kernel, $this->kernel->intranet->get("pdf_header_file_id"));
        }

        $report = new Intraface_modules_invoice_Pdf_Reminder($translation, $filehandler);
        $report->visit($this);
        return $report->output($type, $filename);
    }

    /**
     * returns possible status types
     *
     * @return array possible status types
     */
    private static function getStatusTypes()
    {
        return array(
            0=>'created',
            1=>'sent',
            2=>'executed',
            3=>'cancelled'
        );
    }

    /**
     * returns possible payment methods
     *
     * @return array possible payment methods
     */
    private static function getPaymentMethods()
    {
        return array(
            0=>'Ingen',
            1=>'Kontooverførsel',
            2=>'Girokort +01',
            3=>'Girokort +71'
        );
    }

    /**
     * Gets the payment information
     *
     * @return array
     */
    function getPaymentInformation()
    {
        $info = array('bank_name'    => $this->kernel->setting->get("intranet", "bank_name"),
                      'bank_reg_number' => $this->kernel->setting->get("intranet", "bank_reg_number"),
                      'bank_account_number' => $this->kernel->setting->get("intranet", "bank_account_number"),
                      'giro_account_number' => $this->kernel->setting->get("intranet", "giro_account_number")
        );

        return $info;
    }
}