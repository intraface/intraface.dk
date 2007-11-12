<?php
/**
 * @package Intraface_Procurement
 */

class Procurement Extends Standard {

    var $kernel;
    var $id;
    var $error;
    var $from_region_types;
    var $status_types;
    var $value;
    var $dbquery;

    function __construct($kernel, $id = 0) {

        if(!is_object($kernel) OR strtolower(get_class($kernel)) != 'kernel') {
            trigger_error("Porcurement kræver kernel", E_USER_ERROR);
        }

        $this->kernel = &$kernel;
        $this->error = new Error;
        $this->id = intval($id);

        $module_procurement = $this->kernel->getModule("procurement");
        $this->status_types = $module_procurement->getSetting("status");
        $this->from_region_types = $module_procurement->getSetting("from_region");

        $this->dbquery = new DBQuery($this->kernel, "procurement", "active = 1 AND intranet_id = ".$this->kernel->intranet->get("id"));
        $this->dbquery->useErrorObject($this->error);

        if($this->id != 0) {
            $this->load();
        }
    }

    function load() {

        $db = new DB_sql;

        $db->query("SELECT *,
            DATE_FORMAT(invoice_date, '%d-%m-%Y') AS dk_invoice_date,
            DATE_FORMAT(delivery_date, '%d-%m-%Y') AS dk_delivery_date,
            DATE_FORMAT(payment_date, '%d-%m-%Y') AS dk_payment_date,
            DATE_FORMAT(paid_date, '%d-%m-%Y') AS dk_paid_date,
            DATE_FORMAT(date_recieved, '%d-%m-%Y') AS dk_date_recieved,
            DATE_FORMAT(date_canceled, '%d-%m-%Y') AS dk_date_canceled,
            DATE_FORMAT(date_stated, '%d-%m-%Y') AS date_stated,
            DATE_FORMAT(date_stated, '%d-%m-%Y') AS date_stated_dk

            FROM procurement WHERE intranet_id = ".$this->kernel->intranet->get("id")." AND id = ".$this->id);
        if(!$db->nextRecord()) {
            return false;
        }

        $this->value["id"] = $db->f("id");

        $this->value["invoice_date"] = $db->f("invoice_date");
        if($db->f("invoice_date") != "0000-00-00") {
            $this->value["dk_invoice_date"] = $db->f("dk_invoice_date");
        }
        else {
            $this->value["dk_invoice_date"] = "";
        }

        $this->value["delivery_date"] = $db->f("delivery_date");
        if($db->f("delivery_date") != "0000-00-00") {
            $this->value["dk_delivery_date"] = $db->f("dk_delivery_date");
        }
        else {
            $this->value["dk_delivery_date"] = "";
        }

        $this->value["payment_date"] = $db->f("payment_date");
        if($db->f("payment_date") != "0000-00-00") {
            $this->value["dk_payment_date"] = $db->f("dk_payment_date");
        }
        else {
            $this->value["dk_payment_date"] = "";
        }

        $this->value["date_recieved"] = $db->f("date_recieved");
        if($db->f("date_recieved") != "0000-00-00") {
            $this->value["dk_date_recieved"] = $db->f("dk_date_recieved");
        }
        else {
            $this->value["dk_date_recieved"] = "";
        }

        $this->value["date_canceled"] = $db->f("date_canceled");
        if($db->f("date_canceled") != "0000-00-00") {
            $this->value["dk_date_canceled"] = $db->f("dk_date_canceled");
        }
        else {
            $this->value["dk_date_canceled"] = "";
        }

        $this->value["date_stated"] = $db->f('date_stated');
        $this->value["date_stated_dk"] = $db->f('date_stated_dk');

        $this->value["paid_date"] = $db->f("paid_date");
        $this->value["dk_paid_date"] = $db->f("dk_paid_date");
        $this->value["number"] = $db->f("number");
        $this->value["contact_id"] = $db->f("contact_id");

        $this->value['contact'] = "";
        if($this->kernel->user->hasModuleAccess('contact')) {
            $this->kernel->useModule('contact');
            $contact = new Contact($this->kernel, $db->f('contact_id'));
            if($contact->get('id') > 0) {
                $this->value['contact'] = $contact->address->get('name');
            }
        }

        $this->value["vendor"] = $db->f("vendor");
        $this->value["description"] = $db->f("description");
        $this->value["from_region_key"] = $db->f("from_region_key");
        $this->value["from_region"] = $this->from_region_types[$db->f("from_region_key")];
        $module_procurement = $this->kernel->getModule("procurement");
        //$this->value["dk_from_region"] = $module_procurement->getTranslation($this->from_region_types[$db->f("from_region_key")]);


        $this->value["total_price"] = $db->f("total_price");
        $this->value["dk_total_price"] = number_format($db->f("total_price"), 2, ",",".");
        $this->value["total_price_items"] = $db->f("total_price_items");
        $this->value["dk_total_price_items"] = number_format($db->f("total_price_items"), 2, ",",".");
        $this->value["vat"] = $db->f("vat");
        $this->value["dk_vat"] = number_format($db->f("vat"), 2, ",",".");

        $this->value["price_shipment_etc"] = round($this->value["total_price"] - $this->value["total_price_items"] - $this->value["vat"], 2);
        $this->value["dk_price_shipment_etc"] = number_format($this->value["price_shipment_etc"], 2, ",",".");



        $this->value["status_key"] = $db->f("status_key");
        $this->value["status"] = $this->status_types[$db->f("status_key")];
        //$this->value["paid_key"] = $db->f("paid");


        $this->value["state_account_id"] = $db->f("state_account_id");
        $this->value["voucher_number"] = $db->f("voucher_number");

        return true;

    }

    function loadItem($id = 0) {
         $this->item = new ProcurementItem($this, (int)$id);
  }

    function update($input) {
        if (!is_array($input)) {
            trigger_error('Procurement->update(): $input er ikke et array', E_USER_ERROR);
        }
        $db = new DB_sql;

        $input = safeToDb($input);
        $validator = new Validator($this->error);

        $validator->isDate($input["dk_invoice_date"], "Fakturadato er ikke en gyldig dato", "allow_empty");
        $date = new Intraface_Date($input["dk_invoice_date"]);
        if($date->convert2db()) {
            $input["invoice_date"] = $date->get();
        }
        $validator->isDate($input["dk_delivery_date"], "Leveringsdato er ikke en gyldig dato", "allow_empty");
        $date = new Intraface_Date($input["dk_delivery_date"]);
        if($date->convert2db()) {
            $input["delivery_date"] = $date->get();
        }
        else {
            $input['delivery_date'] = $input['invoice_date'];
        }
        $validator->isDate($input["dk_payment_date"], "Betalingsdato er ikke en gyldig dato", "allow_empty");
        $date = new Intraface_Date($input["dk_payment_date"]);
        if($date->convert2db()) {
            $input["payment_date"] = $date->get();
        }
        else {
            $input['payment_date'] = $input['delivery_date'];
        }

        settype($input["number"], "integer");
        $validator->isNumeric($input["number"], "Nummer er ikke et gyldigt nummer", "greater_than_zero");
        $db->query("SELECT id FROM procurement WHERE id != ".$this->id." AND intranet_id = ".$this->kernel->intranet->get("id")." AND number = ".$input["number"]);
        if($db->nextRecord()) {
            $this->error->set("Nummeret er allerede benyttet");
        }

        /*
        settype($input["contact_id"], "integer");
        if($input["contact_id"] != 0) {
            if($this->kernel->user->hasModuleAccess("contact")) {
                $this->kernel->useModule("contact");
                $contact = new Contact($this->kernel, $input["contact_id"]);
                if($contact->get("id") == 0) {
                    $this->error->set("Ugydligt kontakt");
                }
            }
            else {
                $input["contact_id"] = 0;
            }
        }
        */

        $validator->isString($input["vendor"], "Fejl i leverandør", "", "allow_empty");
        $validator->isString($input["description"], "Fejl i beskrivelse", "", "");

        settype($input["from_region_key"], "integer");
        if(!isset($this->from_region_types[$input["from_region_key"]])) {
            $this->error->set("Ugyldig købsregion");
        }

        $validator->isDouble($input["dk_total_price"], "Samlet pris er ikke et gyldigt beløb");
        $amount = new Amount($input["dk_total_price"]);
        if($amount->convert2db()) {
            $input["total_price"] = $amount->get();
        }
        $validator->isDouble($input["dk_total_price_items"], "Varerpris er ikke et gyldigt beløb");
        $amount = new Amount($input["dk_total_price_items"]);
        if($amount->convert2db()) {
            $input["total_price_items"] = $amount->get();
        }
        $validator->isDouble($input["dk_vat"], "Moms er ikke et gyldigt beløb");
        $amount = new Amount($input["dk_vat"]);
        if($amount->convert2db()) {
            $input["vat"] = $amount->get();
        }


        if($this->error->isError()) {
            return false;
        }

        // paid_date = \"".$input["paid_date"]."\",
        // contact_id = ".$input["contact_id"].",


        $sql = "user_id = ".$this->kernel->user->get("id").",
            date_changed = NOW(),
            invoice_date = \"".$input["invoice_date"]."\",
            delivery_date = \"".$input["delivery_date"]."\",
            payment_date = \"".$input["payment_date"]."\",
            number = ".$input["number"].",
            vendor = \"".$input["vendor"]."\",
            description = \"".$input["description"]."\",
            from_region_key = ".$input["from_region"].",
            total_price = ".$input["total_price"].",
            total_price_items = ".$input["total_price_items"].",
            vat = ".$input["vat"]."";



        if($this->id != 0) {
            $db->query("UPDATE procurement SET ".$sql." WHERE id = ".$this->id." AND intranet_id = ".$this->kernel->intranet->get("id"));
        }
        else {
            $db->query("INSERT INTO procurement SET intranet_id = ".$this->kernel->intranet->get("id").", date_created = NOW(), active = 1, ".$sql);
            $this->id = $db->insertedId();
        }
        $this->load();

        return true;
    }

    function getList() {

        $list = array();

        if($this->dbquery->checkFilter("contact_id")) {
            $this->dbquery->setCondition("contact_id = ".intval($this->dbquery->getFilter("contact_id")));
        }

        if($this->dbquery->checkFilter("text")) {
            $this->dbquery->setCondition("(description LIKE \"%".$this->dbquery->getFilter("text")."%\" OR number = \"".$this->dbquery->getFilter("text")."\")");
        }

        if($this->dbquery->checkFilter("from_date")) {
            $date = new Intraface_Date($this->dbquery->getFilter("from_date"));
            if($date->convert2db()) {
                $this->dbquery->setCondition("invoice_date >= \"".$date->get()."\"");
            }
            else {
                $this->error->set("Fra dato er ikke gyldig");
            }
        }


        // Poster med fakturadato før slutdato.
        if($this->dbquery->checkFilter("to_date")) {
            $date = new Intraface_Date($this->dbquery->getFilter("to_date"));
            if($date->convert2db()) {
                $this->dbquery->setCondition("invoice_date <= \"".$date->get()."\"");
            }
            else {
                $this->error->set("Til dato er ikke gyldig");
            }
        }

        if($this->dbquery->checkFilter("status")) {
            if($this->dbquery->getFilter("status") == "-1") {
                // Behøves ikke, den tager alle.

            }
            elseif($this->dbquery->getFilter("status") == "-2") {
                // Not executed = åbne
                /*
                if($this->dbquery->checkFilter("to_date")) {
                    $date = new Intraface_Date($this->dbquery->getFilter("to_date"));
                    if($date->convert2db()) {
                        // Poster der er executed eller canceled efter dato, og sikring at executed stadig er det, da faktura kan sættes tilbage.
                        $this->dbquery->setCondition("(date_executed >= \"".$date->get()."\" AND status_key = 2) OR (date_canceled >= \"".$date->get()."\") OR status_key < 2");
                    }
                }
                else {
                    // Hvis der ikke er nogen dato så tager vi alle dem som på nuværende tidspunkt har status under
                    $this->dbquery->setCondition("status_key < 2");
                }
                */
                $this->dbquery->setCondition("status_key < 1 OR paid_date = \"0000-00-00\"");

            }
            else {
                if($this->dbquery->checkFilter("to_date")) {
                    switch($this->dbquery->getFilter("status")) {
                        case "0":
                            $to_date_field = "date_created";
                            break;

                        case "1":
                            $to_date_field = "date_recieved";
                            break;

                        case "2":
                            $to_date_field = "data_canceled";
                            break;
                    }

                    $date = new Intraface_Date($this->dbquery->getFilter("to_date"));
                    if($date->convert2db()) {
                        $this->dbquery->setCondition($to_date_field." <= \"".$date->get()."\"");
                    }
                }
                else {
                    // tager dem som på nuværende tidspunkt har den angivet status
                    $this->dbquery->setCondition("status_key = ".intval($this->dbquery->getFilter("status")));
                }
            }
        }

        $i = 0;

        $this->dbquery->setSorting("date_created DESC");
        $db = $this->dbquery->getRecordset("*,
            DATE_FORMAT(delivery_date, '%d-%m-%Y') AS dk_delivery_date,
            DATE_FORMAT(payment_date, '%d-%m-%Y') AS dk_payment_date,
            DATE_FORMAT(paid_date, '%d-%m-%Y') AS dk_paid_date");

        while($db->nextRecord()) {
            $list[$i]["id"] = $db->f("id");
            $list[$i]["description"] = $db->f("description");
            $list[$i]["number"] = $db->f("number");
            $list[$i]["vendor"] = $db->f("vendor");
            $list[$i]["status_key"] = $db->f("status_key");
            $list[$i]["status"] = $this->status_types[$db->f("status_key")];
            $list[$i]["delivery_date"] = $db->f("delivery_date");
            $list[$i]["dk_delivery_date"] = $db->f("dk_delivery_date");
            $list[$i]["payment_date"] = $db->f("payment_date");
            $list[$i]["dk_payment_date"] = $db->f("dk_payment_date");
            $list[$i]["paid_date"] = $db->f("paid_date");
            $list[$i]["dk_paid_date"] = $db->f("dk_paid_date");
            $list[$i]["contact_id"] = $db->f("contact_id");
            $list[$i]["total_price"] = $db->f("total_price");
            if($this->kernel->user->hasModuleAccess('contact') && $db->f("contact_id") != 0) {
                $this->kernel->useModule('contact');
                $contact = new Contact($this->kernel, $db->f("contact_id"));
                $list[$i]["contact"] = $contact->address->get('name');
            }
            else {
                $list[$i]["contact"] = "";
            }
            $i++;
        }

        return $list;
    }

    function getMaxNumber() {
        $db = new DB_sql;

        $db->query("SELECT MAX(number) as max_number FROM procurement WHERE intranet_id = ".$this->kernel->intranet->get("id"));
        $db->nextRecord();

        return $db->f("max_number");
    }

    function setStatus($status) {
        $status_key = array_search($status, $this->status_types);
        if($status_key === false) {
            trigger_error("Ugyldigt status: ".$status, FATAL);
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

    function setPaid($dk_paid_date) {

        if($this->get('id') == 0) {
            return false;
        }

        if($this->get('paid_date') != '0000-00-00') {
            $this->error->set('Betaling er allerede registreret');
            return false;
        }

        $validator = new Validator($this->error);

        $validator->isDate($dk_paid_date, "Betalt dato er ikke en gyldig dato");
        $date = new Intraface_Date($dk_paid_date);
        if($date->convert2db()) {
            $paid_date = $date->get();
        }

        $db = new DB_sql;

        $db->query("UPDATE procurement SET paid_date = \"".$paid_date."\" WHERE intranet_id = ".$this->kernel->intranet->get('id')." AND id = ".$this->get('id'));
        $this->load();

        return true;

    }

    function setContact($contact_id) {

        if($this->id == 0) {
            return 0;
        }

        if($this->kernel->user->hasModuleAccess('contact')) {
            $this->kernel->useModule('contact');
            $contact = new Contact($this->kernel, $contact_id);

            if($contact->get('id') != 0) {
                $db = new DB_sql;
                $db->query("UPDATE procurement SET contact_id = ".$contact->get('id').", date_changed = NOW() WHERE id = ".$this->id);
                $this->load();
                return 1;
            }
            else {
                return 0;
            }
        }
        else {
            return 0;
        }
    }

    function getLatest($product_id, $up_to_quantity = 0) {


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

        while($db->nextRecord() && $over_quantity < 3) { // $over_quantity < 3 angiver hvor mange gange mere end det antal som er på lageret man skal kører over.

            $procurement = new Procurement($this->kernel, $db->f('id'));
            $procurement->loadItem();
            $items = $procurement->item->getList();

            foreach($items AS $item) {

                if($item['product_id'] == $product_id) {
                    $list[$i] = $item;
                    $list[$i]['dk_invoice_date'] = $procurement->get('dk_invoice_date');
                    $sum_quantity += $item['quantity'];
                    $list[$i]['sum_quantity'] = $sum_quantity;
                    $i++;
                }
            }
            if($sum_quantity > $up_to_quantity && $up_to_quantity != 0) {
                $over_quantity++;
            }
        }

        return $list;
    }

    function isFilledIn() {
        $db = new DB_Sql;
        $db->query("SELECT id FROM procurement WHERE intranet_id = " . $this->kernel->intranet->get('id'));
        return $db->numRows();
    }

    function any($contact_id) {
        $db = new DB_Sql;
        $db->query("SELECT id FROM procurement WHERE intranet_id = " . $this->kernel->intranet->get('id')." AND contact_id = ".$contact_id." AND active = 1");
        return $db->numRows();
    }

    /**
     * @see Debtor::state();
     */

    function state($year, $voucher_number, $credit_account_id = null) {
        if ($this->isStated()) {
            $this->error->set('Allerede bogført');
            return 0;
        }
        if (!$this->readyForState()) {
            $this->error->set('Ikke klar til bogføring');
            return 0;
        }

        if (!$this->kernel->user->hasModuleAccess('accounting')) {
            trigger_error('Ikke rettigheder til at bogføre', E_USER_ERROR);
        }

        $this->kernel->useModule('accounting');


        $voucher = Voucher::factory($year, $voucher_number);
        $voucher->save(array(
            'voucher_number' => $voucher_number,
            'date' => $this->get('dk_paid_date'),
            'text' => 'Indkøb: ' . $this->get('number') . ' ' . $this->get('description')
        ));


        $debet_account = new Account($year, $this->get('state_account_id'));
        $debet_account_number = $debet_account->get('number');

        if (!$credit_account_id) {
            $credit_account = new Account($year, $year->getSetting('credit_account_id'));
            $credit_account_number = $credit_account->get('number');
        } else {
            $credit_account = new Account($year, $credit_account_id);
            $credit_account_number = $credit_account->get('number');
        }

        if ($credit_account->get('id') == 0) {
            $this->error->set('Kreditorkontoen ikke sat');
            return 0;
        }

        // items

        $input_values = array(
            'voucher_number' => $voucher->get('number'),
            'date' => $this->get('dk_paid_date'),
            'amount' => $this->get("dk_total_price_items"),
            'debet_account_number' => $debet_account_number,
            'credit_account_number' => $credit_account_number,
            'vat_off' => 1,
            'text' => 'Indkøb #' . $this->get('number') . ' - ' . $this->get('description')
        );

        if (!$voucher->saveInDaybook($input_values, false)) {
            $this->error->set('Kunne ikke gemme i kassekladden');
            return false;
        }

        #
        # shipment etch
        #

        if ($this->get("price_shipment_etc") > 0) {

            $input_values = array(
                'voucher_number' => $voucher->get('number'),
                'date' => $this->get('dk_paid_date'),
                'amount' => $this->get("dk_price_shipment_etc"),
                'debet_account_number' => $debet_account_number,
                'credit_account_number' => $credit_account_number,
                'vat_off' => 1,
                'text' => 'Indkøb #' . $this->get('number') . ' - forsendelse mv.'
            );

            if (!$voucher->saveInDaybook($input_values, false)) {
                $this->error->set('Kunne ikke gemme i kassekladden');
                return false;
            }
        }

        // samlet moms på fakturaen
        // opmærksom på at momsbeløbet her er hardcoded - og det bør egentlig tages fra købet?
        $debet_account = new Account($year, $year->getSetting('vat_out_account_id'));

        if (!$credit_account_id) {
            $credit_account = new Account($year, $year->getSetting('credit_account_id'));
        } else {
            $credit_account = new Account($year, $credit_account_id);
        }

        $input_values = array(
                'voucher_number' => $voucher->get('number'),
                'date' => $this->get('dk_paid_date'),
                'amount' => $this->get('dk_vat'), // opmærksom på at vat bliver rigtig defineret
                'debet_account_number' => $debet_account->get('number'),
                'credit_account_number' => $credit_account->get('number'),
                'vat_off' => 1,
                'text' => 'Indkøb #'.$this->get('number').' - ' . $debet_account->get('name')
        );


        if (!$voucher->saveInDaybook($input_values, false)) {
            $this->error->set('Kunne ikke gemme i kassekladden');
            return 0;
        }

        $this->setStated($voucher_number);

        $this->load();

        return 1;


    }

    function setStated($voucher_number) {
        $db = new DB_Sql;
        $db->query("UPDATE procurement SET date_stated = '" . $this->get('paid_date') . "', voucher_number = '".$voucher_number."' WHERE id = " . $this->id . " AND intranet_id = " . $this->kernel->intranet->get('id'));
        return 1;
    }

    function isStated() {
        if ($this->get("date_stated") > '0000-00-00') {
            return 1;
        }
        return 0;
    }

    function readyForState() {
        if (!$this->kernel->user->hasModuleAccess('accounting')) {
            trigger_error('Brugeren har ikke adgang til accounting og burde aldrig få mulighed for at bogføre', FATAL);
        }

        $this->kernel->useModule('accounting');
        $year = new Year($this->kernel);
        if (!$year->get('id')) {
            $this->error->set('Der er ikke sat noget år. <a href="/modules/accounting/years.php">Sæt regnskabsår</a>.');
            return 0;
        }
        // HACK i selve linket
        if (!$year->isDateInYear($this->get('paid_date'))) {
            $this->error->set('Datoen er ikke i det år, der er sat i regnskabsmodulet. <a href="/modules/accounting/years.php">Skift regnskabsår</a>.');
            return 0;
        }

        if ($this->get('state_account_id') == 0) {
            $this->error->set('Der er ikke sat nogen konto at bogføre på!');
        }

        if ($this->error->isError()) {
            return 0;
        }
        return 1;

    }

    function setStateAccountId($id) {
        $db = new DB_Sql;
        $db->query("UPDATE procurement SET state_account_id = " . $id . " WHERE id = " .$this->id);
        $this->load();
        return true;
    }


}
?>