<?php
/**
 * @package Intraface_Procurement
 */
class Procurement extends Intraface_Standard
{
    public $kernel;
    public $id;
    public $error;
    public $from_region_types;
    public $status_types;
    public $value;
    public $dbquery;

    function __construct($kernel, $id = 0)
    {
        $this->kernel = $kernel;
        $this->error = new Intraface_Error;
        $this->id = intval($id);

        $this->dbquery = new Intraface_DBQuery($this->kernel, "procurement", "active = 1 AND intranet_id = ".$this->kernel->intranet->get("id"));
        $this->dbquery->useErrorObject($this->error);

        if ($this->id != 0) {
            $this->load();
        }
    }

    function load()
    {
        $db = new DB_Sql;

        $db->query("SELECT *,
            DATE_FORMAT(invoice_date, '%d-%m-%Y') AS dk_invoice_date,
            DATE_FORMAT(delivery_date, '%d-%m-%Y') AS dk_delivery_date,
            DATE_FORMAT(payment_date, '%d-%m-%Y') AS dk_payment_date,
            DATE_FORMAT(paid_date, '%d-%m-%Y') AS dk_paid_date,
            DATE_FORMAT(date_recieved, '%d-%m-%Y') AS dk_date_recieved,
            DATE_FORMAT(date_canceled, '%d-%m-%Y') AS dk_date_canceled,
            DATE_FORMAT(date_stated, '%d-%m-%Y') AS date_stated,
            DATE_FORMAT(date_stated, '%d-%m-%Y') AS dk_date_stated

            FROM procurement WHERE intranet_id = ".$this->kernel->intranet->get("id")." AND id = ".$this->id);
        if (!$db->nextRecord()) {
            return false;
        }

        $this->value["id"] = $db->f("id");

        $this->value["invoice_date"] = $db->f("invoice_date");
        if ($db->f("invoice_date") != "0000-00-00") {
            $this->value["dk_invoice_date"] = $db->f("dk_invoice_date");
        } else {
            $this->value["dk_invoice_date"] = "";
        }

        $this->value["delivery_date"] = $db->f("delivery_date");
        if ($db->f("delivery_date") != "0000-00-00") {
            $this->value["dk_delivery_date"] = $db->f("dk_delivery_date");
        } else {
            $this->value["dk_delivery_date"] = "";
        }

        $this->value["payment_date"] = $db->f("payment_date");

        if ($db->f("payment_date") != "0000-00-00") {
            $this->value["dk_payment_date"] = $db->f("dk_payment_date");
        } else {
            $this->value["dk_payment_date"] = "";
        }

        $this->value["date_recieved"] = $db->f("date_recieved");
        if ($db->f("date_recieved") != "0000-00-00") {
            $this->value["dk_date_recieved"] = $db->f("dk_date_recieved");
        } else {
            $this->value["dk_date_recieved"] = "";
        }

        $this->value["date_canceled"] = $db->f("date_canceled");
        if ($db->f("date_canceled") != "0000-00-00") {
            $this->value["dk_date_canceled"] = $db->f("dk_date_canceled");
        } else {
            $this->value["dk_date_canceled"] = "";
        }

        $this->value["date_stated"] = $db->f('date_stated');
        $this->value["dk_date_stated"] = $db->f('dk_date_stated');

        $this->value["paid_date"] = $db->f("paid_date");
        // this is used when stating
        $this->value['this_date'] = $this->value["paid_date"];


        $this->value["dk_paid_date"] = $db->f("dk_paid_date");
        $this->value["number"] = $db->f("number");
        $this->value["contact_id"] = $db->f("contact_id");

        $this->value["vendor"] = $db->f("vendor");
        $this->value["description"] = $db->f("description");
        $this->value["from_region_key"] = $db->f("from_region_key");
        $region_types = $this->getRegionTypes();
        $this->value["from_region"] = $region_types[$db->f("from_region_key")];

        $this->value["price_items"] = $db->f("price_items");
        $this->value["dk_price_items"] = number_format($db->f("price_items"), 2, ",",".");
        $this->value["price_shipment_etc"] = $db->f("price_shipment_etc");
        $this->value["dk_price_shipment_etc"] = number_format($this->value["price_shipment_etc"], 2, ",",".");
        $this->value["vat"] = $db->f("vat");
        $this->value["dk_vat"] = number_format($db->f("vat"), 2, ",",".");
        $this->value["total_price"] = round($this->value["price_items"] + $this->value["price_shipment_etc"] + $this->value["vat"], 2);
        $this->value["dk_total_price"] = number_format($this->value["total_price"], 2, ",",".");

        $this->value["status_key"] = $db->f("status_key");
        $types = $this->getStatusTypes();
        $this->value["status"] = $types[$db->f("status_key")];
        //$this->value["paid_key"] = $db->f("paid");

        $this->value["state_account_id"] = $db->f("state_account_id");
        $this->value["voucher_id"] = $db->f("voucher_id");
        return true;

    }

    function loadItem($id = 0)
    {
         $this->item = new ProcurementItem($this, (int)$id);
    }

    function update($input)
    {
        if (!is_array($input)) {
            throw new Exception('Procurement->update(): $input er ikke et array');
        }
        $db = new DB_sql;

        $input = safeToDb($input);
        $validator = new Intraface_Validator($this->error);

        if (!isset($input['dk_invoice_date'])) {
            $input['dk_invoice_date'] = '';
        }
        $validator->isDate($input["dk_invoice_date"], "Fakturadato er ikke en gyldig dato");
        $date = new Intraface_Date($input["dk_invoice_date"]);
        if ($date->convert2db()) {
            $input["invoice_date"] = $date->get();
        } else {
            $input["invoice_date"] = '';
        }

        if (!isset($input['dk_delivery_date'])) {
            $input['dk_delivery_date'] = '';
        }
        $validator->isDate($input["dk_delivery_date"], "Leveringsdato er ikke en gyldig dato", "allow_empty");
        $date = new Intraface_Date($input["dk_delivery_date"]);
        if ($date->convert2db()) {
            $input["delivery_date"] = $date->get();
        } else {
            $input['delivery_date'] = $input['invoice_date'];
        }

        if (!isset($input['dk_payment_date'])) {
            $input['dk_payment_date'] = '';
        }
        $validator->isDate($input["dk_payment_date"], "Betalingsdato er ikke en gyldig dato", "allow_empty");
        $date = new Intraface_Date($input["dk_payment_date"]);
        if ($date->convert2db()) {
            $input["payment_date"] = $date->get();
        } else {
            $input['payment_date'] = $input['delivery_date'];
        }

        if (empty($input['number'])) {
            $input['number'] = 0;
        }
        $validator->isNumeric($input["number"], "Nummer er ikke et gyldigt nummer", "greater_than_zero");
        $db->query("SELECT id FROM procurement WHERE id != ".$this->id." AND intranet_id = ".$this->kernel->intranet->get("id")." AND number = ".$input["number"]);
        if ($db->nextRecord()) {
            $this->error->set("Nummeret er allerede benyttet");
        }

        if (!isset($input['vendor'])) {
            $input['vendor'] = '';
        }
        $validator->isString($input["vendor"], "Fejl i leverand�r", "", "allow_empty");

        if (!isset($input['description'])) {
            $input['description'] = '';
        }
        $validator->isString($input["description"], "Fejl i beskrivelse", "", "");

        if (!isset($input['from_region_key'])) {
            $input['from_region_key'] = 0;
        }
        $region_types = $this->getRegionTypes();
        if (!isset($region_types[$input["from_region_key"]])) {
            $this->error->set("Ugyldig k�bsregion");
        }

        if (!isset($input['dk_price_items'])) {
            $input['dk_price_items'] = 0;
        }
        $validator->isDouble($input["dk_price_items"], "Varepris er ikke et gyldigt beløb", 'zero_or_greater');
        $amount = new Intraface_Amount($input["dk_price_items"]);
        if ($amount->convert2db()) {
            $input["price_items"] = $amount->get();
        }

        if (!isset($input['dk_price_shipment_etc'])) {
            $input['dk_price_shipment_etc'] = 0;
        }
        $validator->isDouble($input["dk_price_shipment_etc"], "Pris for forsendelse og andet er ikke et gyldigt beløb", 'zero_or_greater');
        $amount = new Intraface_Amount($input["dk_price_shipment_etc"]);
        if ($amount->convert2db()) {
            $input["price_shipment_etc"] = $amount->get();
        }

        if (!isset($input['dk_vat'])) {
            $input['dk_vat'] = 0;
        }
        $validator->isDouble($input["dk_vat"], "Moms er ikke et gyldigt beløb", 'zero_or_greater');
        $amount = new Intraface_Amount($input["dk_vat"]);
        if ($amount->convert2db()) {
            $input["vat"] = $amount->get();
        }

        if ($this->error->isError()) {
            return false;
        }

        $sql = "user_id = ".$this->kernel->user->get("id").",
            date_changed = NOW(),
            invoice_date = \"".$input["invoice_date"]."\",
            delivery_date = \"".$input["delivery_date"]."\",
            payment_date = \"".$input["payment_date"]."\",
            number = ".$input["number"].",
            vendor = \"".$input["vendor"]."\",
            description = \"".$input["description"]."\",
            from_region_key = ".$input["from_region_key"].",
            price_items = ".$input["price_items"].",
            price_shipment_etc = ".$input["price_shipment_etc"].",
            vat = ".$input["vat"]."";

        if ($this->id != 0) {
            $db->query("UPDATE procurement SET ".$sql." WHERE id = ".$this->id." AND intranet_id = ".$this->kernel->intranet->get("id"));
        } else {
            $db->query("INSERT INTO procurement SET intranet_id = ".$this->kernel->intranet->get("id").", date_created = NOW(), active = 1, ".$sql);
            $this->id = $db->insertedId();
        }
        $this->load();

        return true;
    }

    function setStatus($status)
    {
        $status_key = array_search($status, $this->getStatusTypes());
        if ($status_key === false) {
            throw new Exception("Ugyldigt status: ".$status, FATAL);
        }

        switch($status) {
            case "ordered":
                $sql = "";
                break;

            case "recieved":
                $sql = ", date_recieved = NOW()";
                break;

            case "canceled":
                $sql = ", date_canceled = NOW()";
                break;
        }

        $db = new DB_sql;
        $db->query("UPDATE procurement SET status_key = ".$status_key." ".$sql." WHERE id = ".$this->id." AND intranet_id = ".$this->kernel->intranet->get("id"));

        $this->load();

        return true;
    }

    function setPaid($dk_paid_date)
    {
        if ($this->get('id') == 0) {
            return false;
        }

        if ($this->get('paid_date') != '0000-00-00') {
            $this->error->set('Betaling er allerede registreret');
            return false;
        }

        $validator = new Intraface_Validator($this->error);

        $validator->isDate($dk_paid_date, "Betalt dato er ikke en gyldig dato");
        $date = new Intraface_Date($dk_paid_date);
        if ($date->convert2db()) {
            $paid_date = $date->get();
        }

        $db = new DB_sql;

        $db->query("UPDATE procurement SET paid_date = \"".$paid_date."\" WHERE intranet_id = ".$this->kernel->intranet->get('id')." AND id = ".$this->get('id'));
        $this->load();

        return true;
    }

    function setContact($contact)
    {
        if ($this->id == 0) {
            return 0;
        }

        if (!is_object($contact)) {
            throw new Exception('The parameter to set Contact need to be a contact object!');
            return false;
        }

        if ($contact->get('id') == 0) {
            throw new Exception('The given contact is not valid!');
            return false;
        }

        $db = new DB_sql;
        $db->query("UPDATE procurement SET contact_id = ".$contact->get('id').", date_changed = NOW() WHERE id = ".$this->id);
        $this->load();
        return true;
    }

    function getLatest($product_id, $up_to_quantity = 0)
    {
        $sum_quantity = 0;
        $list = array();
        $i = 0;
        $over_quantity = 0;

        $db = new DB_sql;
        $db->query("SELECT DISTINCT(procurement.id) FROM procurement
            INNER JOIN procurement_item ON procurement.id = procurement_item.procurement_id
            WHERE procurement_item.active = 1 AND procurement.active = 1
                AND procurement_item.intranet_id = ".$this->kernel->intranet->get("id")." AND procurement.intranet_id = ".$this->kernel->intranet->get("id")."
                AND procurement_item.product_id = ".$product_id." AND procurement.status_key = 1 ORDER BY procurement.invoice_date DESC, procurement_item.id ASC");

        while ($db->nextRecord() && $over_quantity < 3) { // $over_quantity < 3 angiver hvor mange gange mere end det antal som er pï¿½ lageret man skal kï¿½rer over.

            $procurement = new Procurement($this->kernel, $db->f('id'));
            $procurement->loadItem();
            $items = $procurement->item->getList();

            foreach ($items AS $item) {

                if ($item['product_id'] == $product_id) {
                    $list[$i] = $item;
                    $list[$i]['dk_invoice_date'] = $procurement->get('dk_invoice_date');
                    $sum_quantity += $item['quantity'];
                    $list[$i]['sum_quantity'] = $sum_quantity;
                    $i++;
                }
            }
            if ($sum_quantity > $up_to_quantity && $up_to_quantity != 0) {
                $over_quantity++;
            }
        }

        return $list;
    }

    /**
     * State procurement
     *
     * @param object year year object
     * @param integer voucher_number
     *
     * @return boolean
     */
    function state($year, $voucher_number, $voucher_date, $debet_accounts, $credit_account_id, $translation)
    {
        if (!is_object($year)) {
            throw new Exception('First parameter to state needs to be a Year object!');
        }

        if (!is_object($translation)) {
            throw new Exception('Sixth parameter to state needs to be a Translation object!');
            return false;
        }

        if (!$this->readyForState($year)) {
            $this->error->set('Ikke klar til bogføring');
            return false;
        }

        if (!$this->checkStateDebetAccounts($year, $debet_accounts, 'skip_amount_check')) {
            return false;
        }

        $validator = new Intraface_Validator($this->error);
        if ($validator->isDate($voucher_date, "Ugyldig dato")) {
            $voucher_date_object = new Intraface_Date($voucher_date);
            $voucher_date_object->convert2db();
        }

        if (!$year->isDateInYear($voucher_date_object->get())) {
            $this->error->set('Datoen er ikke i det år, der er sat i regnskabsmodulet.');
        }

        $credit_account = Account::factory($year, $credit_account_id);
        if (!$credit_account->validForState()) {
            $this->error->set('Ugyldig konto hvor indkøbet er betalt fra');
            return false;
        }

        $validator->isNumeric($voucher_number, 'Ugyldigt bilagsnummer', 'greater_than_zero');

        if ($this->error->isError()) {
            return false;
        }


        $text = $translation->get('procurement').'# ' . $this->get('number') . ': ' . $this->get('description');
        require_once 'Intraface/modules/accounting/Voucher.php';
        $voucher = Voucher::factory($year, $voucher_number);
        $voucher->save(array(
            'voucher_number' => $voucher_number,
            'date' => $voucher_date,
            'text' => $text
        ));

        $credit_total = 0;
        foreach ($debet_accounts as $key => $line) {
            $debet_account = Account::factory($year, $line['state_account_id']);

            $amount = new Intraface_Amount($line['amount']);
            $amount->convert2db();
            $amount = $amount->get();
            $credit_total += $amount;

            if (!empty($line['text'])) {
                $line_text = $text. ' - ' . $line['text'];
            } else {
                $line_text = $text;
            }

            // if the amount is stated on an account with vat we add the vat on the amount!
            if ($debet_account->get('vat') == 'in') {
                $amount += round($amount/100*$debet_account->get('vat_percent'), 2);
            }

            $input_values = array(
                'voucher_number' => $voucher->get('number'),
                'date' => $voucher_date,
                'amount' => number_format($amount, 2, ',', ''),
                'debet_account_number' => $debet_account->get('number'),
                'credit_account_number' => $credit_account->get('number'),
                'vat_off' => 0,
                'text' => $line_text
            );

            if (!$voucher->saveInDaybook($input_values, true)) {
                $this->error->merge($voucher->error->getMessage());
            }

        }

        require_once 'Intraface/modules/accounting/VoucherFile.php';
        $voucher_file = new VoucherFile($voucher);
        if (!$voucher_file->save(array('description' => $text, 'belong_to'=>'procurement','belong_to_id'=>$this->get('id')))) {
            $this->error->merge($voucher_file->error->getMessage());
            $this->error->set('Filen blev ikke overflyttet');
        }

        if ($this->error->isError()) {
            $this->error->set('Der er opstået en fejl under bogføringen af indkøbet. Det kan betyde at dele af den er bogført, men ikke det hele. Du bedes manuelt tjekke bilaget');
            // I am not quite sure if the procurement should be set as stated, but it can give trouble to state it again, if some of it was stated...
            $this->setStated($voucher->get('id'), $voucher_date);
            return false;
        }

        $this->setStated($voucher->get('id'), $voucher_date);
        $this->load();
        return true;
    }

    function setStated($voucher_number, $voucher_date)
    {
        $db = new DB_Sql;

        $validator = new Intraface_Validator($this->error);
        if ($validator->isDate($voucher_date, "Ugyldig dato")) {
            $voucher_date = new Intraface_Date($voucher_date);
            $voucher_date->convert2db();
        }

        $db->query("UPDATE procurement SET date_stated = '".$voucher_date->get()."', voucher_id = ".intval($voucher_number)." WHERE id = " . $this->id . " AND intranet_id = " . $this->kernel->intranet->get('id'));
        return 1;
    }

    function isStated()
    {
        if ($this->get("date_stated") > '0000-00-00') {
            return true;
        }
        return false;
    }

    /**
     * returns whether the procurement is ready for state
     *
     * @param object year accounting year
     * @return boolean true or false
     */
    function readyForState($year)
    {
        if (!is_object($year)) {
            throw new Exception('First parameter to readyForState needs to be a Year object!');
            return false;
        }

        if (!$year->readyForState($this->get('paid_date'))) {
            $this->error->set('Regnskabï¿½ret er ikke klar til bogfï¿½ring.');
            return false;
        }

        if ($this->get('id') == 0) {
            $this->error->set('Indkï¿½bet er ikke gemt');
            return false;
        }

        if ($this->get("paid_date") == "0000-00-00") {
            $this->error->set('Indkï¿½bet skal vï¿½re betalt for at det kan bogfï¿½res.');
        }

        if ($this->isStated()) {
            $this->error->set('Indkï¿½bet er allerede bogfï¿½rt');
            return false;
        }

        if ($this->error->isError()) {
            return false;
        }
        return true;
    }

    /**
     * Checks whether the debet accounts is valid
     */
    public function checkStateDebetAccounts($year, $debet_accounts, $skip_amount_check = 'do_amount_check')
    {
        if (!is_object($year)) {
            throw new Exception('First parameter to checkStateDebetAccounts needs to be a Year object!');
            return false;
        }

        if (!is_array($debet_accounts)) {
            throw new Exception('Second parameter to checkStateDebetAccounts needs to be an array');
            return false;
        }

        if (!in_array($skip_amount_check, array('do_amount_check', 'skip_amount_check'))) {
            throw new Exception('Third parameter to checkStateDebetAccounts needs to be either do_amount_check or skip_amount_check');
            return false;
        }

        if (empty($debet_accounts)) {
            $this->error->set('you have not set any debet accounts');
            return false;
        }

        $validator = new Intraface_Validator($this->error);

        $total = 0;
        $vat = 0;
        foreach ($debet_accounts AS $key => $debet_account) {
            if ($validator->isNumeric($debet_account['amount'], 'Ugyldig belï¿½b i linje '.($key+1).' "'.$debet_account['text'].'"', 'greater_than_zero')) {

                $amount = new Intraface_Amount($debet_account['amount']);
                $amount->convert2db();
                $total += $amount->get();

                $validator->isString($debet_account['text'], 'Ugyldig tekst i linje '.($key+1).' "'.$debet_account['text'].'"', '', 'allow_empty');

                if (empty($debet_account['state_account_id']) ) {
                    $this->error->set('Linje '.($key+1).' "'.$debet_account['text'].'" ved ikke hvor den skal bogfï¿½res');
                } else {
                    require_once 'Intraface/modules/accounting/Account.php';
                    $account = Account::factory($year, $debet_account['state_account_id']);

                    // @todo check this. I changed it to make sure that we are able to state varekï¿½b til videresalg
                    // || $account->get('type') != 'operating'
                    if ($account->get('id') == 0) {
                        $this->error->set('Ugyldig konto for bogfï¿½ring af linje '.($key+1).' "'.$debet_account['text'].'"');
                    } elseif ($account->get('vat') == 'in') {

                        $vat += $amount->get()/100*$account->get('vat_percent');
                    }
                }
            }
        }

        if ($this->error->isError()) {
            return false;
        }

        if ($skip_amount_check == 'do_amount_check') {
            if (round($total + $this->get('vat'), 2) != $this->get('total_price')) {
                $delta = $this->get('total_price') - ($total + $this->get('vat'));
                $this->error->set('Det samlede beløb ('.number_format($total + $this->get('vat'), 2, ',', '.').') til bogføring stemmer ikke overens med det samlede beløb på indkøbet ('.number_format($this->get('total_price'), 2, ',', '.').'). Der er en forskel på '.number_format($delta, 2, ',', '.').'. Har du fået alle varer på indkøbet med?');
            }

            if (round($vat, 2) != $this->get('vat')) {
                $expected = number_format($this->get('vat') * 4, 2, ',', '.');
                $this->error->set('Momsen af de beløb du bogfører på konti med moms stemmer (vi forventede '.number_format($vat, 2, ',', '.').') ikke overens med momsen på det samlede indkøb (det samlede beløb burde have været '.$expected.'). Har du fået alle varer med? Har du husket at skrive beløbet uden moms for varerne?');
            }
        }

        if ($this->error->isError()) {
            return false;
        }
        return true;
    }

    /**
     * returns possible status types
     *
     * @todo: duplicate in Procurement class
     *
     * @return array status types
     */
    private function getStatusTypes()
    {
        return array(
            0 => 'ordered',
            1 => 'recieved',
            2 => 'canceled'
        );
    }

    /**
     * returns the possible regions where procurement is bought
     * @todo: duplicate in Procurement class
     *
     * @return array possible regions
     */
    public function getRegionTypes()
    {
        return array(
            0 => 'denmark',
            1 => 'eu',
            2 => 'eu_vat_registered',
            3 => 'outside_eu'
        );
    }

    function getItems()
    {
        $this->loadItem();
        $items = $this->item->getList();
        return $items = $this->item->getList();
    }
}