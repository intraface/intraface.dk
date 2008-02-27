<?php
/**
 * Klasse bruges til at registere betaling af faktura, og rykker(gebyr)
 * @package Intraface_Invoice
 *
 * @author Sune Jensen <sj@sunet.dk>
 * @version: 1
 */

require_once 'Intraface/Standard.php';

class Payment extends Standard
{
    protected $id;
    public $kernel;
    protected $payment_for;
    protected $payment_for_type_id;
    protected $payment_for_id;
    public $error;
    public $dbquery;
    private $db;

    function __construct($object, $id = 0)
    {
        if (!is_object($object)) {
            trigger_error('First parameter for Payment needs to be a invoice or reminder object', E_USER_ERROR);
            return false;
        }

        $this->kernel      = $object->kernel;
        $this->payment_for = $object;
        $this->error       = $object->error;
        $this->payment_for_type_id = array_search(strtolower(get_class($object)), $this->getPaymentForTypes());
        $this->payment_for_id = $this->payment_for->get("id");

        if ($this->payment_for_type_id === false) {
            trigger_error('Payment can only be for either Invoice or reminder', E_USER_ERROR);
            return false;
        }

        $this->id = intval($id);

        $this->dbquery = new DBQuery($this->kernel, "invoice_payment", "intranet_id = ".$this->kernel->intranet->get("id")." AND payment_for = ".$this->payment_for_type_id." AND payment_for_id = ".$this->payment_for_id.' AND type IN ('.implode(',', array_keys($this->getTypes())).')');
        $this->dbquery->useErrorObject($this->error);
        $this->db = MDB2::singleton(DB_DSN);

        if ($this->id != 0) {
            $this->load();
        }
    }

    public function load()
    {
        $result = $this->db->query('SELECT id, amount, type, description, payment_date, payment_for_id, DATE_FORMAT(payment_date, "%d-%m-%Y") AS dk_payment_date, date_stated, voucher_id FROM invoice_payment ' .
            'WHERE intranet_id = '.$this->kernel->intranet->get('id').' ' .
                'AND payment_for = '.$this->payment_for_type_id.' ' .
                'AND payment_for_id = '.$this->payment_for_id.' ' .
                'AND type IN ('.implode(',', array_keys($this->getTypes())).')' .
                'AND id = '.$this->id);

        if (PEAR::isError($result)) {
            trigger_error('Error in query '.$result->getUserInfo(), E_USER_ERROR);
            return false;
        }

        if (!$this->value = $result->fetchRow(MDB2_FETCHMODE_ASSOC)) {
            $this->value['id'] = 0;
            return false;
        }

        $this->value['type_key'] = $this->value['type'];
        $types = $this->getTypes();
        $this->value['type'] = $types[$this->value['type_key']];

    }

    function update($input = "")
    {
        if ($this->payment_for_type_id == 0) {
            trigger_error('Invalid paymet_for_type_id in Payment->update', E_USER_ERROR);
            return false;
        }
        if ($this->payment_for_id == 0) {
            trigger_error('Invalid paymet_for_id in Payment->update', E_USER_ERROR);
            return false;
        }

        // Man har mulighed for at køre $payment->update() bare for at få den til at
        // sætte invoice eller reminder til executed
        if (!is_array($input)) {
            if (is_object($this->payment_for)) {
                $this->payment_for->updateStatus();
                return true;
            }
        }

        $input = safeToDb($input);
        $validator = new Validator($this->error);

        if (!isset($input["payment_date"])) $input["payment_date"] = '';
        if ($validator->isDate($input["payment_date"], "Ugyldig dato", "allow_no_year")) {
            $date = new Intraface_Date($input["payment_date"]);
            $date->convert2db();
        }

        if (!isset($input["amount"])) $input["amount"] = 0;
        if ($validator->isDouble($input["amount"], "Ugyldig beløb")) {
            $amount = new Amount($input["amount"]);
            $amount->convert2db();
            $amount = $amount->get();
        }

        if (!isset($input['description'])) $input['description'] = '';
        $validator->isString($input["description"], "Fejl i beskrivelse", "", "allow_empty");

        if (!isset($input['type'])) $input['type'] = NULL;
        $validator->isNumeric($input["type"], "Type er ikke angivet korrekt");
        $types = $this->getTypes();
        if (!isset($types[$input["type"]])) {
            $this->error->set("Ugyldig type");
        }

        if ($this->error->isError()) {
            return false;
        }

        $sql = "payment_date = \"".$date->get()."\",
            amount = ".$amount.",
            type = ".$input["type"].",
            description = \"".$input["description"]."\",
            payment_for = ".$this->payment_for_type_id.",
            payment_for_id = ".$this->payment_for_id;

        $db = new DB_sql;
        $db->query("INSERT INTO invoice_payment SET intranet_id = ".$this->kernel->intranet->get("id").", ".$sql);

        $this->id = $db->insertedId();
        $this->load();

        if (is_object($this->payment_for)) {
            $this->payment_for->load();
            $this->payment_for->updateStatus();
        }
        return true;
    }

    function getList()
    {

        $db = new DB_sql;
        $i = 0;
        $payment = array();
        $credit_note = array();

        if ($this->dbquery->checkFilter("to_date")) {
            $date = new Intraface_Date($this->dbquery->getFilter("to_date"));
            if ($date->convert2db()) {
                $this->dbquery->setCondition("payment_date <= \"".$date->get()."\"");
            }
        }

        $this->dbquery->setSorting("payment_date ASC");
        $db = $this->dbquery->getRecordset("id, amount, type, description, payment_date, payment_for_id, DATE_FORMAT(payment_date, '%d-%m-%Y') AS dk_payment_date, date_stated, voucher_id", "", false);
        while($db->nextRecord()) {
            $payment[$i]["id"] = $db->f("id");
            $types = $this->getTypes();
            $payment[$i]["type"] = $types[$db->f('type')];
            $payment[$i]["amount"] = $db->f("amount");
            $payment[$i]["description"] = $db->f("description");
            $payment[$i]["payment_date"] = $db->f("payment_date");
            $payment[$i]["dk_payment_date"] = $db->f("dk_payment_date");
            $payment[$i]["is_stated"] = ($db->f('date_stated') > '0000-00-00');
            $payment[$i]["voucher_id"] = $db->f("voucher_id");
            $payment[$i]["payment_for_id"] = $db->f("payment_for_id");
            $i++;
        }

        return $payment;
    }

    /*
    // Old getList. 22/1 2008
    function getList()
    {

        $db = new DB_sql;
        $value = array(); // type(invoice,payment,credit_note,reminder), description, date, amount
        $i = 0;
        $payment = array();
        $credit_note = array();

        // Hent betalinger
        if (is_object($this->payment_for)) {

            if ($this->dbquery->checkFilter("to_date")) {
                $date = new Intraface_Date($this->dbquery->getFilter("to_date"));
                if ($date->convert2db()) {
                    $this->dbquery->setCondition("payment_date <= \"".$date->get()."\"");
                }
            }

            $this->dbquery->setSorting("payment_date ASC");
            $db = $this->dbquery->getRecordset("id, amount, type, description, payment_date, payment_for_id, DATE_FORMAT(payment_date, '%d-%m-%Y') AS dk_payment_date, date_stated, voucher_id", "", false);
            while($db->nextRecord()) {
                $payment[$i]["id"] = $db->f("id");
                if ($db->f("type") == -1) {

                    $payment[$i]["type"] = "depriciation";
                    $payment[$i]["amount"] = $db->f("amount");
                    $payment[$i]["description"] = $db->f("description");
                } else {
                    $types = $this->getTypes();
                    $payment[$i]["type"] = $types[$db->f('type')];
                    // $payment[$i]["type"] = "payment";
                    $payment[$i]["amount"] = $db->f("amount");
                    $payment[$i]["description"] = $db->f("description");
                }
                $payment[$i]["payment_date"] = $db->f("payment_date");
                $payment[$i]["dk_payment_date"] = $db->f("dk_payment_date");
                $payment[$i]["is_stated"] = ($db->f('date_stated') > '0000-00-00');
                $payment[$i]["voucher_id"] = $db->f("voucher_id");

                // $payment[$i]["payment_for"] = $this->payment_for[$db->f("payment_for")];
                $payment[$i]["payment_for_id"] = $db->f("payment_for_id");

                $i++;
            }
        }


        // Hent kreditnotaer. Ikke hvis det er en reminder. Den kan ikke krediteres.
        if (strtolower(get_class($this->payment_for)) !== "reminder") {
            require_once 'Intraface/modules/invoice/CreditNote.php';
            $debtor = new CreditNote($this->kernel);
            // Hvis det er en faktura
            if (strtolower(get_class($this->payment_for)) == "invoice") {
                $debtor->dbquery->setCondition("where_from = 5 AND where_from_id = ".$this->payment_for->get("id"));
                $debtor->dbquery->setSorting("this_date");
            } else {
                // Hvis det ikke er faktura, så er det en søgning på alle betalinger for kontakt.
                trigger_error("Betalinger for en contact er ikke implementeret", E_USER_ERROR);
                // Følgende kan vist kun være noget lort. contact_id og intranet_id
                $debtor->dbquery->setCondition("contact_id = ".$this->kernel->intranet->get("id"));
                $debtor->dbquery->setSorting("this_date");
            }
            // Det er ret krævende at køre debtor->getList(), måske det burde gøres med direkte sql-udtræk.
            $credit_note = $debtor->getList();
        }


        $pay = 0; // payment
        $pay_max = count($payment);

        $inv = 0; // invoice

        $cre = 0; // credit_note
        $cre_max = count($credit_note);
        $i = 0;

        while($pay < $pay_max || $cre < $cre_max) {



            $next = "";

            if (isset($payment[$pay]["payment_date"]) && $payment[$pay]["payment_date"] != "") {
                $pay_date = strtotime($payment[$pay]["payment_date"]);
            } else {
                $pay_date = 0;
            }

            if (isset($credit_note[$cre]['this_date']) && $credit_note[$cre]["this_date"] != "") {
                $cre_date = strtotime($credit_note[$cre]["this_date"]);
            } else {
                $cre_date = 0;
            }

            if ($pay_date != 0) {
                $next = "payment";
            } elseif ($cre_date != 0) {
                $next = "credit_note";
            }

            if ($cre_date != 0 && $cre_date < $pay_date) $next = "credit_note";

            if ($next == "payment") {

                $value[$i]["type"] = $payment[$pay]["type"];
                //$value[$i]["dk_type"] = $payment[$pay]["dk_type"];
                $value[$i]["id"] = $payment[$pay]["id"];
                $value[$i]["date"] = $payment[$pay]["payment_date"];
                $value[$i]["dk_date"] = $payment[$pay]["dk_payment_date"];
                $value[$i]["description"] = $payment[$pay]["description"];
                $value[$i]["amount"] = $payment[$pay]["amount"];
                $value[$i]['is_stated'] = $payment[$pay]['is_stated'];
                $value[$i]['voucher_id'] = $payment[$pay]['voucher_id'];
                $pay++;
            } elseif ($next == "credit_note") {
                $value[$i]["type"] = "credit_note";
                $value[$i]["id"] = $credit_note[$cre]["id"];
                $value[$i]["date"] = $credit_note[$cre]["this_date"];
                $value[$i]["dk_date"] = $credit_note[$cre]["dk_this_date"];
                if ($credit_note[$cre]["description"] != "") {
                    $value[$i]["description"] = $credit_note[$cre]["description"];
                }
                else {
                    $value[$i]["description"] = "[Ingen beskrivelse]";
                }
                $value[$i]["amount"] = $credit_note[$cre]["total"];
                $value[$i]['is_stated'] = $credit_note[$cre]['is_stated'];
                $value[$i]['voucher_id'] = $credit_note[$cre]['voucher_id'];
                $cre++;
            }

            $i++;
        }

        return $value;
    }
    */

    function readyForState()
    {
        if ($this->get('id') == 0) {
            $this->error->set('Betaling er ikke gemt eller loaded');
            return false;

        }

        if ($this->isStated()) {
            $this->error->set('Betalingen er allerede bogført');
            return false;
        }

        return true;
    }

    /**
     * return whether the payment is stated
     *
     * @return boolean true or false
     */
    function isStated()
    {
        if ($this->id == 0) {
            return false;
        } elseif ($this->get('date_stated') > '0000-00-00') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * States the payment i the given year
     *
     * @param object $year Accounting Year object
     * @param integer $voucher_number
     * @param string $voucher_date
     * @param integer $state_account_number
     *
     * @return boolean true on succes or false.
     */
    public function state($year, $voucher_number, $voucher_date, $state_account_number, $translation)
    {
        if (!is_object($year)) {
            trigger_error('First parameter to state needs to be a Year object!', E_USER_ERROR);
            return false;
        }

        if (!is_object($translation)) {
            trigger_error('5th parameter to state needs to be a translation object!', E_USER_ERROR);
            return false;
        }

        if ($this->payment_for_type_id == 0) {
            trigger_error('Invalid paymet_for_type_id in Payment->state', E_USER_ERROR);
            return false;
        }

        $validator = new Validator($this->error);
        if ($validator->isDate($voucher_date, "Ugyldig dato")) {
            $this_date = new Intraface_Date($voucher_date);
            $this_date->convert2db();
        }

        $validator->isNumeric($voucher_number, 'Ugyldigt bilagsnummer', 'greater_than_zero');
        $validator->isNumeric($state_account_number, 'Ugyldig bogføringskonto', 'greater_than_zero');

        if (!$this->readyForState()) {
            return false;
        }

        if (!$year->readyForState()) {
            $this->error->merge($year->error->getMessage());
            return false;
        }

        // this should be a method in Year instead
        require_once 'Intraface/modules/accounting/Account.php';
        $credit_account = new Account($year, $year->getSetting('debtor_account_id'));
        if (!$credit_account->validForState()) {
            $this->error->set('Den gemte debitorkonto er ikke gyldig til bogføring');
            return false;
        }
        $credit_account_number = $credit_account->get('number');

        $debet_account = Account::factory($year, $state_account_number);
        if (!$debet_account->validForState()) {
            $this->error->set('Den valgte konto for bogføring er ikke gyldig');
            return false;
        }
        $debet_account_number = $debet_account->get('number');

        require_once 'Intraface/modules/accounting/Voucher.php';
        $voucher = Voucher::factory($year, $voucher_number);
        $amount = $this->get('amount');

        // hvis beløbet er mindre end nul, skal konti byttes om og beløbet skal gøres positivt
        if ($amount < 0) {
            $debet_account_number = $credit_account->get('number');
            $credit_account_number = $debet_account->get('number');
            $amount = abs($amount);
        }

        $types = $this->getPaymentForTypes();
        // translation is needed!
        $text = $translation->get('payment for').' '.$translation->get($types[$this->payment_for_type_id]).' #'.$this->payment_for->get('number');

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
            return false;
        }

        $db = new DB_sql;
        $db->query("UPDATE invoice_payment SET date_stated = NOW(), voucher_id = ".$voucher->get('id'));

        $this->load();
        return true;
    }

    /**
     * returns possible payment types
     *
     * @return array payment types
     *
     */
    public static function getTypes()
    {
        return array(
            0 => 'bank_transfer',
            1 => 'giro_transfer',
            2 => 'credit_card',
            3 => 'cash');
    }

    /**
     * returns the possible types payments can be for.
     *
     * @return array payment for types
     */
    private static function getPaymentForTypes()
    {
        return array(
            0 => 'manuel',
            1 => 'invoice',
            2 => 'reminder');
    }
}