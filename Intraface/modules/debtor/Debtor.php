<?php
/**
 * Debtor = debitor
 *
 * Debitorklassen bruges til både tilbud og ordrer. Den bruges fra et modul,
 * og så bygges de andre på som moduler, der benytter det overordnede modul.
 *
 * Klassen kan også bruges til at styre fakturaer.
 *
 * @author Lars Olesen <lars@legestue.net>
 */

class Debtor extends Standard {
    var $id;
    var $kernel;
    var $contact;
    var $contact_person;
    var $value = array();
    var $error;
    var $type;
    var $type_key;
    var $db;
    var $allowed_from;
    var $allowed_status;
    var $allowed_types;
    var $payment_methods;
    var $dbquery;
    var $payment;

    /**
     * Constructor
     *
     * @param $kernel (object)
     * @param $type (string) - se også $allowed_types
     * @param $id (integer) Debtor-id
     */
    function __construct($kernel, $type, $id = 0) {
        // sørger for at vi har det rigtige objekt
        // denne bør ikke have brug for typen.
        if (!is_object($kernel)) {
            trigger_error('Debtor kræver Kernel som objekt', E_USER_ERROR);
        }

        $this->kernel = $kernel;

        #
        # Hente settings
        #
        $debtorModule = $this->kernel->getModule('debtor');
        $this->allowed_types = $debtorModule->getSetting('type');
        $this->allowed_from = $debtorModule->getSetting('from');
        $this->allowed_status = $debtorModule->getSetting('status');
        $this->payment_methods = $debtorModule->getSetting('payment_method');

        $this->type = $type;
        $this->type_key = array_search($type, $this->allowed_types);
        if(!isset($this->type_key)) {
            trigger_error('Debtor: Ugyldig type', E_USER_ERROR);
        }

        #
        # Her sætter vi lige type selvom den ikke er loaded, da man nogle
        # gange skal bruge type med id = 0 i getList
        #
        $this->value["type"] = $this->type;
        $this->value["type_key"] = $this->type_key;

        #
        # sætter variable
        #
        $this->id = (int)$id;
        $this->db = new DB_Sql;
        $this->error = new Error;

        $this->dbquery = new DBQuery($this->kernel, "debtor", "debtor.active = 1 AND debtor.intranet_id = ".$this->kernel->intranet->get("id"));
        $this->dbquery->setJoin("LEFT", "contact", "debtor.contact_id = contact.id AND contact.intranet_id = ".$this->kernel->intranet->get("id"), '');
        $this->dbquery->setJoin("LEFT", "address", "address.belong_to_id = contact.id AND address.active = 1 AND address.type = 3", '');
        $this->dbquery->setJoin("LEFT", "debtor_item", "debtor_item.debtor_id = debtor.id AND debtor_item.active = 1 AND debtor_item.intranet_id = ".$this->kernel->intranet->get("id"), '');


        // Ved ikke hvorfor den er udkommenteret /Sune
        $this->dbquery->useErrorObject($this->error);

        if ($this->id > 0) {
            $this->load();
        }

    }

    // vi bør vende det her om og finde ud af en standard factory måde at gøre det på
    // mit forslag: factory($kernel, $type, $id)

    function factory(&$kernel, $id = 0, $type = "") {
        if((int)$id != 0) {
            $debtor = $kernel->useModule("debtor");
            $types = $debtor->getSetting("type");

            $db = new DB_Sql;
            $db->query("SELECT type FROM debtor WHERE intranet_id = ".$kernel->intranet->get('id')." AND id = ".$id);
            if($db->nextRecord()) {
                $type = $types[$db->f("type")];
            }
            else {
                trigger_error("Invalid id for debtor in Debtor::factory", E_USER_ERROR);

            }
        }

        switch($type) {
            case "quotation":
                $kernel->useModule("quotation");
                $object = new Quotation($kernel, intval($id));
                return $object;
                break;

            case "order":
                $kernel->useModule("order");
                $object = new Order($kernel, intval($id));
                break;

            case "invoice":
                $kernel->useModule("invoice");
                $object = new Invoice($kernel, intval($id));
                break;

            case "credit_note":
                $kernel->useModule("invoice");
                $object = new CreditNote($kernel, intval($id));
                break;

            default:
                trigger_error("Ugyldig type", E_USER_ERROR);
                break;
        }

        return $object;

    }

  /**
     * load()
     * Funktionen loader debtoren ind i et array();
     *
     * @see $this->values;
     * return 0 / debtorid
     */
    function load() {
        if($this->id == 0) {
            return 0;
        }

        $this->db->query("SELECT id, number, intranet_address_id, contact_id, contact_address_id, contact_person_id, description, payment_method, this_date, due_date, date_stated, voucher_id, date_executed, status, where_from_id, where_from, user_id, round_off, girocode, active, message, internal_note,
                DATE_FORMAT(date_stated, '%d-%m-%Y') AS dk_date_stated,
                DATE_FORMAT(this_date, '%d-%m-%Y') AS dk_this_date,
                DATE_FORMAT(due_date, '%d-%m-%Y') AS dk_due_date,
                DATE_FORMAT(date_sent, '%d-%m-%Y') AS dk_date_sent,
                DATE_FORMAT(date_executed, '%d-%m-%Y') AS dk_date_executed,
                DATE_FORMAT(date_cancelled, '%d-%m-%Y') AS dk_date_cancelled
            FROM debtor WHERE id = ".$this->id." AND intranet_id = ".$this->kernel->intranet->get("id"));

        if(!$this->db->nextRecord()) {
            $this->error->set('Debtoren findes ikke');
            return 0;
        }
        $this->value["id"] = $this->db->f("id");
        $this->value["number"] = $this->db->f("number");
        $this->value["intranet_address_id"] = $this->db->f("intranet_address_id");
        $this->value["contact_id"] = $this->db->f("contact_id");
        $this->value["contact_address_id"] = $this->db->f("contact_address_id");
        $this->value["contact_person_id"] = $this->db->f("contact_person_id");
        $this->value["description"] = $this->db->f("description");
        if (empty($this->value["description"])) {
            $this->value["description"] = '[Ingen beskrivelse]';
        }
        $this->value["payment_method"] = $this->db->f("payment_method");
        $this->value["translated_payment_method"] = $this->payment_methods[$this->db->f("payment_method")];
        $this->value["this_date"] = $this->db->f("this_date");
        $this->value["due_date"] = $this->db->f("due_date");
        $this->value["date_stated"] = $this->db->f("date_stated");
        //$this->value["voucher_number"] = $this->db->f("voucher_number");
        $this->value["voucher_id"] = $this->db->f("voucher_id");
        $this->value["dk_date_stated"] = $this->db->f("dk_date_stated");
        $this->value["dk_this_date"] = $this->db->f("dk_this_date");
        $this->value["dk_due_date"] = $this->db->f("dk_due_date");
        $this->value["dk_date_sent"] = $this->db->f("dk_date_sent");
        $this->value["date_executed"] = $this->db->f("date_executed");
        $this->value["dk_date_executed"] = $this->db->f("dk_date_executed");
        $this->value["dk_date_cancelled"] = $this->db->f("dk_date_cancelled");
        $this->value["status"] = $this->allowed_status[$this->db->f("status")];
        $this->value["status_id"] = $this->db->f("status");
        // $this->value["is_credited"] = $this->db->f("is_credited");
        $this->value["where_from"] = $this->allowed_from[$this->db->f("where_from")];
        $this->value["where_from_id"] = $this->db->f("where_from_id");
        $this->value["user_id"] = $this->db->f("user_id");
        $this->value["round_off"] = $this->db->f("round_off");
        $this->value["girocode"] = $this->db->f("girocode");
        $this->value["message"] = $this->db->f("message");
        $this->value["internal_note"] = $this->db->f("internal_note");
        $this->value["active"] = $this->db->f("active");


        #
        # Bruges til at afgøre, hvor debtor er sendt hent til
        #
        $db = new DB_Sql;
        $db->query("SELECT id, type FROM debtor WHERE where_from_id = " . $this->id . " AND active = 1");
        if ($db->nextRecord()) {

            if ($db->f('type') > 0) {
                $this->value['where_to'] = $this->allowed_types[$db->f('type')];
            }
            else {
                $this->value['where_to'] = '';
            }
            $this->value['where_to_id'] = $db->f('id');
        }
        else {
            $this->value['where_to'] = '';
            $this->value['where_to_id'] = 0;
        }



        if($this->get("status") == "executed" || $this->get("status") == "cancelled") {
            $this->value["locked"] = true;
        }
        else {
            $this->value["locked"] = false;
        }

        // henter kunden
        $this->contact = new Contact($this->kernel, $this->db->f("contact_id"), $this->db->f("contact_address_id"));
        if($this->contact->get("type") == "corporation" && $this->db->f("contact_person_id") != 0) {
            $this->contact_person = new ContactPerson($this->contact, $this->db->f("contact_person_id"));
        }

        // henter items på debtoren

        $this->loadItem();
        $item = $this->item->getList();
        $this->value['items'] = $item;

        for($i = 0, $max = count($item), $total = 0; $i<$max; $i++) {
            $total += $item[$i]["amount"];
        }


        if($this->get("round_off") == 1 && $this->get("type") == "invoice") {
            $decimal = $total - floor($total);
            $decimal *= 4;
            $decimal = round($decimal)/4;
            $total = $decimal + floor($total);
        }

        $this->value["total"] = round($total, 2);
        $this->value['payment_total'] = 0;
        $this->value['payment_online'] = 0;


        if($this->value["type"] == "invoice") {
            $payment = new Payment($this);
            $payments = $payment->getList();
            for($i = 0, $max = count($payments); $i < $max; $i++) {
                $this->value['payment_total'] += $payments[$i]["amount"];
            }
        }


        if(($this->value["type"] == "order" || $this->value["type"] == "invoice") && $this->kernel->intranet->hasModuleAccess('onlinepayment')) {

            $this->kernel->useModule('onlinepayment', true); // true: only look after intranet access
            $onlinepayment = OnlinePayment::factory($this->kernel);
            $onlinepayment->dbquery->setFilter('belong_to', $this->value["type"]);
            $onlinepayment->dbquery->setFilter('belong_to_id', $this->value['id']);
            $onlinepayment->dbquery->setFilter('status', 2);


            // $actions = $onlinepayment->getTransactionActions();

            $payment_list = $onlinepayment->getlist();
            foreach($payment_list AS $p) {
                if($p['status'] == 'authorized') {
                    // Det er kune ikke hævede beløb der skal regnes med. Hævede beløb regnes med under betalinger.
                    $this->value['payment_online'] += $p["amount"];
                }
            }
        }

        $this->value['arrears'] = $this->value['total'] - $this->value['payment_total'];

        return 1;
    }

    function getIntranetAddress()
    {
        return new Address($this->get("intranet_address_id"));
    }

  /**
   * Sætter status for debtoren
   *
   * @return true / false
   */
    function setStatus($status) {

        if(is_string($status)) {
            $status_id = array_search($status, $this->allowed_status);
            if($status_id === false) {
                trigger_error("Debtor->setStatus(): Ugyldig status (streng)", E_USER_ERROR);
            }
        }
        else{
            $status_id = intval($status);
            if(isset($sthis->allowed_status[$status_id])) {
                $status = $this->allowed_status[$status];
            }
            else {
                trigger_error("Debtor->setStatus(): Ugyldig status (integer)", E_USER_ERROR);
            }
        }


        if($status_id == $this->get("status_id")) {
            trigger_error("Du kan ikke sætte status til samme som den er i forvejen", E_USER_ERROR);
        }
        if(($this->get("type") != "invoice" && $status_id < $this->get("status_id")) || ($this->get("type") == "invoice" && $this->get("status") != "executed" && $status_id < $this->get("status_id"))) {
            // Man kan godt gå fra executed til sent, hvis f.eks. en betalt faktura bliver efterfølgende bliver krediteret
            trigger_error("Du kan ikke sætte status lavere end den er i forvejen", E_USER_ERROR);
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
                trigger_error("Dette kan ikke lade sig gøre! Debtor->setStatus()", E_USER_ERROR);
        }

        $db = new Db_Sql;
        $db->query("UPDATE debtor SET status = ".$status_id.", ".$sql."  WHERE id = " . $this->id . " AND intranet_id = " . $this->kernel->intranet->get('id'));
        $this->load();

        return true;
    }

  /**
   * Sætter hvorfra debtoren er indtastet. Bør kun bruges, hvis ikke det stammer fra modulet selv,
   * fx fra en webshop, eller hvor de bliver skabt fra et andet sted, fx en ordre skabt fra et tilbud-
   *
   * @return true / false
   */
    function setFrom($from = 'manuel', $from_id = 0) {
        $from = array_search($from, $this->allowed_from);
        if(!isset($from)) {
            trigger_error('Debtor->setFrom(): Ugyldig from', E_USER_ERROR);
        }
        $from_id = (int)$from_id;

        if ($this->error->count() > 0) {
            return 0;
        }
        $db = new Db_Sql;
        $db->query("UPDATE debtor SET where_from = ".(int)$from.", where_from_id = " . $from_id . " WHERE id = " . $this->id);
        return 1;
    }

    /**
   * Kan være nyttigt i forbindelse med webshop, at man kan logge ip'en.
   *
   */
    function setIp($ip) {
        die('Bruges ikke endnu');
        $db = new Db_Sql;
        $db->query("UPDATE debtor SET ip = '".$ip."' WHERE id = " . $this->id);
        return 1;
    }

    /**
     * update()
     *
     * @param $input (array)
     * @param $from (string) Bruges til at fortælle, hvor debtoren kommer fra, fx webshop eller quotation
     * @param $from_id (integer) Hvis debtoren kommer fra en anden debtor.
     */
    function update($input, $from = 'manuel', $from_id = 0) {
        if (!is_array($input)) {
            trigger_error('Debtor->update(): $input er ikke et array', E_USER_ERROR);
        }

        if ($this->get('locked') == true) {
            $this->error->set('Posten er låst og kan ikke opdateres');
            return 0;
        }

        $input = safeToDb($input);
        $from = safeToDb($from);
        $from_id = (int)$from_id;


        // starte validatoren
        $validator = new Validator($this->error);

        // nummeret
        if (empty($input["number"])) {
            $input["number"] = $this->getMaxNumber() + 1;
        }
        $validator->isNumeric($input["number"], "Nummeret (".$input["number"].")  skal være et tal", "greater_than_zero");
        if(!$this->isNumberFree($input["number"])) {
            $this->error->set("Nummeret er allerede benyttet");
        }

        // kunde
        $validator->isNumeric($input["contact_id"], "Du skal angive en kunde", "greater_than_zero");
        $contact = new Contact($this->kernel, $input["contact_id"]);
        if(is_object($contact->address)) {
            $contact_address_id = $contact->address->get("address_id");
        }
        else {
            $this->error->set("Ugyldig kunde");
        }

        if($contact->get("type") == "corporation") {
            $validator->isNumeric($input["contact_person_id"], "Der er ikke angivet en kontaktperson");
        }
        else {
            $input["contact_person_id"] = 0;
        }
        $validator->isString($input['description'], 'Fejl i descr.', '', 'allow_empty');

        if($validator->isDate($input["this_date"], "Ugyldig dato", "allow_no_year")) {
            $this_date = new Intraface_Date($input["this_date"]);
            $this_date->convert2db();
        }
        if($this->type == "invoice") {
          // Hvis det er en faktura skal der indtastes en due_date, ellers er det ligegyldigt!
            if($validator->isDate($input["due_date"], "Ugyldig leveringsdato", "allow_no_year")) {
                $due_date = new Intraface_Date($input["due_date"]);
                $due_date->convert2db();
                $due_date_db = $due_date->get();
            }
        }
        else {
            if($validator->isDate($input["due_date"], "Ugyldig leveringsdato", "allow_no_year,allow_empty")) {
                // der skal laves en due-date, som bare bliver dags datoe, hvis ikke der er indtastet nogen.
                if (!empty($input["due_date"])) {
                    $due_date = new Intraface_Date($input["due_date"]);
                    $due_date->convert2db();
                    $due_date_db = $due_date->get();
                }
                else {
                    $due_date_db = date('Y-m-d');
                }
            }
        }

        settype($input['payment_method'], 'integer');
        settype($input['girocode'], 'string');

        $internal_note_sql = '';
        if (isset($input['internal_note'])) {
            $internal_note_sql = ", internal_note = '".$input['internal_note']."'";
        }


        if(isset($input["round_off"]) && intval($input["round_off"])) {
            $input["round_off"] = 1;
        }
        else {
            $input["round_off"] = 0;
        }

        if($this->error->isError()) {
            return 0;
        }
      // user_id = ".$this->kernel->user->get('id').", // skal puttes på, men kun hvis det ikke er fra webshop.
        $db = new DB_Sql;
        if ($this->id == 0) {
            $sql_type = "INSERT INTO ";
            $sql_after = ", date_created = NOW(), intranet_id = " . $this->kernel->intranet->get('id');
        }
        else {
            $sql_type = "UPDATE ";
            $sql_after = " WHERE id = " . $this->id . " AND intranet_id = " . $this->kernel->intranet->get('id');
        }
        $sql = $sql_type . "debtor SET contact_id = " . $contact->get('id') . ",
            contact_address_id = ".$contact_address_id.",
            contact_person_id = ".$input['contact_person_id'].",
            intranet_address_id = ".$this->kernel->intranet->address->get("address_id").",
            date_changed = NOW(),
            due_date = '".$due_date_db."',
            this_date = '".$this_date->get()."',
            type = '".$this->type_key."',
            number='".$input['number']."',
            description = '".$input['description']."',
            message = '".$input['message']."',
            round_off = ".$input["round_off"].",
            payment_method=".$input['payment_method'].",
            girocode='".$input['girocode']."' " . $internal_note_sql . $sql_after;

            // attention_to = '". $input['attention_to'] ."',

        $db->query($sql);

        if($this->id == 0) {
            $this->id = $db->insertedId();
            $this->setFrom($from, $from_id);
        }

        if (is_object($this->kernel->user) AND strtolower(get_class($this->kernel->user)) == 'user') {
            $db->query("UPDATE debtor SET user_id = ".$this->kernel->user->get('id')." WHERE id = " . $this->id);
        }

        $this->load();

        return (int)$this->id;

    }

    /**
     * setNewContact();
     * Bruges til at skifte kunden på en debtor, er fx nyttig ved webshopordrene, hvis
     * kunden allerede findes i systemet.
     * Måske den burde indholde en kontrol af at kunden overhovedet findes og tilhøre dette intranet /Sune (21/3 2005)
     *
     * @param $contact_id int
     */

    function setNewContact($contact_id) {
        if ($this->id == 0) {
            return 0;
        }
      $contact_id = (int)$contact_id;
      $db = new DB_Sql;
        $db->query("UPDATE debtor SET contact_id = " . $contact_id . " WHERE id = " . $this->id . " AND intranet_id = " . $this->kernel->intranet->get("id") . " AND type='".$this->type_key."'");

        return 1;
  }

    /**
     * Bruges til at lave en menu på kontakten eller produktet
     *
     * @param string $type: contact eller product
     * @param integer $type_id: id på contact eller product.
     *
     */
    function any($type, $type_id) {

        switch($type) {
            case 'contact':
                $sql = "SELECT id
                FROM debtor
                    WHERE intranet_id = " . $this->kernel->intranet->get("id") . "
                        AND contact_id = ".(int)$type_id."
              AND type='".$this->type_key."'
              AND active = 1";
            break;
            case 'product':
                $sql = "SELECT DISTINCT(debtor.id)
                    FROM debtor
                    INNER JOIN debtor_item ON debtor_item.debtor_id = debtor.id
                    WHERE debtor.intranet_id = ".$this->kernel->intranet->get("id")."
                        AND debtor.type=".$this->type_key."
                        AND debtor.active = 1
                        AND debtor_item.intranet_id = ".$this->kernel->intranet->get("id")."
                        AND debtor_item.active = 1
                        AND debtor_item.product_id = ".(int)$type_id;
            break;
            default:
                trigger_error("Ugyldg type i Debtor->any", E_USER_ERROR);
        }

        $db = new DB_Sql;
        // print($sql);
        $db->query($sql);
        return $db->numRows();
    }

    /**
   *
   *
   */

    function getList() {
        $db = new DB_Sql;

        $this->dbquery->setCondition("debtor.type = ".$this->get("type_key"));

        if($this->dbquery->checkFilter("contact_id")) {
            $this->dbquery->setCondition("debtor.contact_id = ".intval($this->dbquery->getFilter("contact_id")));
        }



        if($this->dbquery->checkFilter("text")) {
            $this->dbquery->setCondition("(debtor.description LIKE \"%".$this->dbquery->getFilter("text")."%\" OR debtor.girocode = \"".$this->dbquery->getFilter("text")."\" OR debtor.number = \"".$this->dbquery->getFilter("text")."\" OR address.name LIKE \"%".$this->dbquery->getFilter("text")."%\")");
        }

        if($this->dbquery->checkFilter("from_date")) {
            $date = new Intraface_Date($this->dbquery->getFilter("from_date"));
            if($date->convert2db()) {
                $this->dbquery->setCondition("debtor.this_date >= \"".$date->get()."\"");
            }
            else {
                $this->error->set("Fra dato er ikke gyldig");
            }
        }

        if($this->dbquery->checkFilter("product_id")) {
            $this->dbquery->setCondition("debtor_item.product_id = ".$this->dbquery->getFilter('product_id'));
        }


        // Poster med fakturadato før slutdato.
        if($this->dbquery->checkFilter("to_date")) {
            $date = new Intraface_Date($this->dbquery->getFilter("to_date"));
            if($date->convert2db()) {
                $this->dbquery->setCondition("debtor.this_date <= \"".$date->get()."\"");
            }
            else {
                $this->error->set("Til dato er ikke gyldig");
            }
        }
        // alle ikke bogførte skal findes
        if($this->dbquery->checkFilter("not_stated")) {
            $this->dbquery->setCondition("voucher_id = 0");

        }


        if($this->dbquery->checkFilter("status")) {
            if($this->dbquery->getFilter("status") == "-1") {
                // Behøves ikke, den tager alle.
                // $this->dbquery->setCondition("status >= 0");

            }
            elseif($this->dbquery->getFilter("status") == "-2") {
                // Not executed = åbne
                if($this->dbquery->checkFilter("to_date")) {
                    $date = new Intraface_Date($this->dbquery->getFilter("to_date"));
                    if($date->convert2db()) {
                        // Poster der er executed eller cancelled efter dato, og sikring at executed stadig er det, da faktura kan sættes tilbage.
                        $this->dbquery->setCondition("(debtor.date_executed >= \"".$date->get()."\" AND debtor.status = 2) OR (debtor.date_cancelled >= \"".$date->get()."\") OR debtor.status < 2");
                    }
                }
                else {
                    // Hvis der ikke er nogen dato så tager vi alle dem som på nuværende tidspunkt har status under
                    $this->dbquery->setCondition("debtor.status < 2");
                }

            }
            elseif($this->dbquery->getFilter("status") == "-3") {
                //  Afskrevne. Vi tager først alle sendte og executed.

                if($this->get("type") != "invoice") {
                    trigger_error("Afskrevne kan kun benyttes ved faktura", E_USER_ERROR);
                }

                $this->dbquery->setJoin("INNER", "invoice_payment", "invoice_payment.payment_for_id = debtor.id", "invoice_payment.intranet_id = ".$this->kernel->intranet->get("id")." AND invoice_payment.payment_for = 1");
                $this->dbquery->setCondition("invoice_payment.type = -1");

                if($this->dbquery->checkFilter("to_date")) {
                    $date = new Intraface_Date($this->dbquery->getFilter("to_date"));
                    if($date->convert2db()) {
                        // alle som er sendte på datoen og som ikke er cancelled
                        $this->dbquery->setCondition("debtor.date_sent <= '".$date->get()."' AND debtor.status != 3");
                        $this->dbquery->setCondition("invoice_payment.payment_date <= '".$date->get()."'");
                    }
                }
                else {
                    // Hvis der ikke er nogen dato så tager vi alle dem som på nuværende tidspunkt har status under
                    $this->dbquery->setCondition("status = 1 OR status = 2");
                }
            }
            else {
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
                        $to_date_field = "data_cancelled";
                        break;
                }

                if($this->dbquery->checkFilter("to_date")) {
                    $date = new Intraface_Date($this->dbquery->getFilter("to_date"));
                    if($date->convert2db()) {
                        $this->dbquery->setCondition("debtor.".$to_date_field." <= \"".$date->get()."\"");
                    }
                }
                else {
                    // tager dem som på nuværende tidspunkt har den angivet status
                    $this->dbquery->setCondition("debtor.status = ".intval($this->dbquery->getFilter("status")));
                }
            }
        }

        switch($this->dbquery->getFilter("sorting")) {
            case 1:
                $this->dbquery->setSorting("debtor.number ASC");
                break;
            case 2:
                $this->dbquery->setSorting("contact.number ASC");
                break;
            case 3:
                $this->dbquery->setSorting("address.name ASC");
                break;
            default:
                $this->dbquery->setSorting("debtor.number DESC");
        }



        $db = $this->dbquery->getRecordset("DISTINCT(debtor.id)", "", false);
        $i = 0;
        $list = array();


        while($db->nextRecord()) {

            $debtor = Debtor::factory($this->kernel, $db->f("id"));
            $list[$i] = $debtor->get();

            // $contact = new Contact($this->kernel, $db->f('contact_id'));
            if (is_object($debtor->contact->address)) {
                $list[$i]['contact'] = $debtor->contact->get();
                $list[$i]['contact']['address'] = $debtor->contact->address->get();

                // følgende skal væk
                $list[$i]['contact_id'] = $debtor->contact->get('id');
                $list[$i]['name'] = $debtor->contact->address->get('name');
                $list[$i]['address'] = $debtor->contact->address->get('address');
                $list[$i]['postalcode'] = $debtor->contact->address->get('postcode');
                $list[$i]['city'] = $debtor->contact->address->get('city');


            }

            /*
            if($this->get("type") == "invoice") {
                $payments = $debtor->getPayments($this->dbquery->getFilter("to_date"));
                $list[$i]['deprication'] = $payments["deprication"]; // denne skal væk

                $list[$i]['payment'] = $payments;
                $list[$i]['arrears'] = $list[$i]['total'] - $payments['total'];
            }
            */

            $i++;

        }
        return $list;
    }


    /**
     * Må kun bruges hvis hele ordren skal slettes
     */

    function delete() {
        if ($this->id > 0 AND $this->get("locked") == true) {
            $this->error->set('Posten er låst og kan ikke slettes');
            return 0;
        }
        $db = new DB_Sql;
        $db->query("SELECT id FROM debtor WHERE id = " . $this->id . " AND intranet_id = " . $this->kernel->intranet->get('id'). " AND type = '".$this->type_key."' LIMIT 1");
        if ($db->nextRecord()) {
            $db->query("UPDATE debtor SET active = 0 WHERE id = " . $this->id . " AND intranet_id = " . $this->kernel->intranet->get('id') . "  AND type = '".$this->type_key."'");
            return 1;
        }
        return 0;
    }

    function isNumberFree($number) {
        $number = safeToDb($number);
        $db = new DB_Sql;
        $sql = "SELECT id FROM debtor WHERE intranet_id = " . $this->kernel->intranet->get('id') . " AND number = '".$number."' AND type = '".$this->type_key."' AND id != " . $this->id." AND active = 1";
        $db->query($sql);
        if ($db->numRows() == 0) {
            return 1;
        }
        return 0;

    }

    function getMaxNumber() {
        $db = new DB_Sql;
        $db->query("SELECT MAX(number) AS max_number FROM debtor WHERE intranet_id = ".$this->kernel->intranet->get("id")." AND type=".$this->type_key." AND active = 1");
        if ($db->nextRecord()) {
            return $db->f('max_number');
        }
        return 0;
    }

    function loadItem($id = 0) {
        $this->item = new DebtorItem($this, (int)$id);
    }

    function getItems() {
        $this->loadItem();
        return $this->item->getList();
    }

    function addItem() {}


    /**
     * Funktionen bruges til at gemme et debitorobjekt som et andet.
     * Denne funktion er nyttig, når jeg fx skal transformere et tilbud til en ordre. Så smider
     * jeg bare et objekt med tilbuddet ind i et nyt objekt jeg skaber med ordre, og så går
     * det hele automatisk.
     *
     * TODO Kunne være vakst om den lige gav det nye objekt en beskrivelse.
     *
     * Fx
     * $quot_id = 10;
     * $quot = new Debtor($kernel, 'quotation', $quot_id);
       * $order = new Debtor($kernel, 'order');
     * $order->create($quot);
     *
     * @return int = id på den nye debtor, der skabes.
     */

    function create(& $debtor_object) {
        if (!is_object($debtor_object)) {
            trigger_error('Debtor: create() har brug for et debtor-objekt, jeg kan skabe et nyt debtorobjekt med', E_USER_ERROR);
        }

        if($debtor_object->get("type") == "invoice") {
            // Faktura kan godt krediteres selvom den er låst.
            if($debtor_object->get("status") == "created" || $debtor_object->get("status") == "cancelled") {
                $this->error->set('Debtor::Created kan ikke lave kreditnota fra faktura, når fakturaen ikke er sendt eller færdigbehandlet', E_USER_ERROR);
                return false;
            }
        }
        else {
            if($debtor_object->get('locked') == true) {
                $this->error->set('Objektet er låst, så du kan ikke lave et nyt objekt fra det.', E_USER_ERROR);
                return false;
            }
        }

        $values = $debtor_object->get();
        $values['this_date'] = date('d-m-Y');
        $values['number'] = ''; // nulstiller nummeret ellers vil den få samme nummer

        switch($this->type) {
            case "invoice":
                $values['due_date'] = date("d-m-Y", time() + 24 * 60 * 60 * $debtor_object->contact->get("paymentcondition"));
                if ($this->kernel->setting->get('intranet', 'bank_account_number')) {
                    $values['payment_method'] = 1;
                }
                break;
            case "order":
            default:
                $values['due_date'] = date('d-m-Y');
                break;
        }

        if($new_debtor_id = $this->update($values, $debtor_object->get("type"), $debtor_object->get('id'))) {
            $debtor_object->loadItem();
            $items = $debtor_object->item->getList();

            foreach ($items AS $item) {
                $this->loadItem();
                $debtor_object->loadItem($item['id']);
                $item_values = $debtor_object->item->get();
                $item_values["quantity"] = number_format($item_values["quantity"], 2, ",", "");
                $this->item->save($item_values);
            }

            if($this->type != "credit_note") {
                // Hvis det er en credit_note, så skal fakturanet ikke låses, da man ikke ved om kreditnotaen er på hele fakturaen
                $debtor_object->setStatus('executed');
            }


            // Overførsel af onlinebetaling fra ordre til faktura.
            if($debtor_object->get('type') == "order" && $this->kernel->intranet->hasModuleAccess('onlinepayment')) {
                $onlinepayment_module = $this->kernel->useModule('onlinepayment', true); // true: ignore user permisssion
                $onlinepayment = OnlinePayment::factory($this->kernel);

                $onlinepayment->dbquery->setFilter('belong_to', 'order');
                $onlinepayment->dbquery->setFilter('belong_to_id', $debtor_object->get('id'));
                $payment_list = $onlinepayment->getlist();

                foreach($payment_list AS $p) {
                    $tmp_onlinepayment = OnlinePayment::factory($this->kernel, 'id', $p['id']);
                    $tmp_onlinepayment->changeBelongTo('invoice', $new_debtor_id);
                }
            }



            return $new_debtor_id;
        }
        return 0;
    }
    /*
    function getText($key) {

        switch($this->type) {
            case "quotation":
                $text["title"] = "tilbud";
                $text["title_new"] = "Nyt tilbud";
                $text["title_edit"] = "Ret tilbud";
                $text["due_date"] = "Udløbsdato";
                $text["number"] = "Tilbudsnr.";
                $text["create_new"] = "Opret nyt tilbud";
                $text["content"] = "Tilbudsindhold";
                break;

            case "order":
                $text["title"] = "ordre";
                $text["title_new"] = "Ny ordre";
                $text["title_edit"] = "Ret ordre";
                $text["due_date"] = "Leveringsdato";
                $text["number"] = "Ordrenr.";
                $text["create_new"] = "Opret ny ordre";
                $text["content"] = "Ordreindhold";
                break;

            case "invoice":
                $text["title"] = "faktura";
                $text["title_new"] = "Ny faktura";
                $text["title_edit"] = "Ret faktura";
                $text["due_date"] = "Forfaldsdato";
                $text["number"] = "Fakturanr.";
                $text["create_new"] = "Opret ny faktura";
                $text["content"] = "Fakturaindhold";
                break;

            case "credit_note":
                $text["title"] = "kreditnota";
                $text["title_new"] = "Ny kreditnota";
                $text["title_edit"] = "Ret kreditnota";
                $text["due_date"] = "";
                $text["number"] = "Kreditnotanr.";
                $text["create_new"] = "Opret ny kreditnota";
                $text["content"] = "Kreditnotaindhold";
                break;
        }

        return($text[$key]);
    }
    */
    /* FUNKTIONER TIL BOGFØRING ***********************************************************/

    function readyForState() {
        if (!$this->kernel->user->hasModuleAccess('accounting')) {
            trigger_error('Brugeren har ikke adgang til accounting og burde aldrig få mulighed for at bogføre', E_USER_ERROR);
        }
        $accounting_module = $this->kernel->useModule('accounting');
        $year = new Year($this->kernel);
        if (!$year->get('id')) {
            $this->error->set('Der er ikke sat noget år. <a href="'.$accounting_module->getPath().'years.php">Sæt regnskabsår</a>.');
        }
        elseif (!$year->isDateInYear($this->get('this_date'))) {
                $this->error->set('Datoen er ikke i det år, der er sat i regnskabsmodulet. <a href="'.$accounting_module->getPath().'years.php">Skift regnskabsår</a>.');
        }
        elseif($year->get('locked') == 1) {
                $this->error->set('Året er ikke åbent for bogføring.');
        }

        if ($this->error->isError()) {
            return 0;
        }
        return 1;

    }

    function setStated($voucher_id, $voucher_date) {
        // FIXME - check on date
        $db = new DB_Sql;
        $db->query("UPDATE debtor SET date_stated = '" . $voucher_date . "', voucher_id = '".$voucher_id."' WHERE id = " . $this->id . " AND intranet_id = " . $this->kernel->intranet->get('id'));
        return 1;
    }

    function isStated() {
        if ($this->get("date_stated") > '0000-00-00') {
            return 1;
        }
        return 0;
    }


    /**
     * Funktion til at finde ud af, om der er oprettet nogen poster af den aktuelle bruger
     */

    function isFilledIn() {
        $db = new DB_Sql;
        $db->query("SELECT id FROM debtor WHERE type = " . $this->type_key . " AND intranet_id = " . $this->kernel->intranet->get('id'));
        return $db->numRows();
    }

    function getInvoiceText()
    {
        return $this->kernel->setting->get('intranet', 'debtor.invoice.text');
    }

    function getContactInformation()
    {
        $intranet = array();
        switch($this->kernel->setting->get('intranet', 'debtor.sender')) {
            case 'intranet':
                // void
                break;
            case 'user':
                $intranet['email'] = $this->kernel->user->address->get('email');
                $intranet['contact_person'] = $this->kernel->user->address->get('name');
                $intranet['phone'] = $this->kernel->user->address->get('phone');
                break;
            case 'defined':
                $intranet['email'] = $this->kernel->setting->get('intranet', 'debtor.sender.email');
                $intranet['contact_person'] = $this->kernel->setting->get('intranet', 'debtor.sender.name');
                break;
        }

        return $intranet;
    }


    function pdf($type = '', $filename = '') {
        if($this->get('id') == 0) {
            trigger_error('Cannot create pdf from debtor without valid id', E_USER_ERROR);
        }

        $shared_pdf = $this->kernel->useShared('pdf');
        $shared_pdf->includeFile('PdfMakerDebtor.php');

        $translation = $this->kernel->getTranslation('debtor');

        // hmm this should be done with the module object
        require_once PATH_INCLUDE_MODULE . 'debtor/Visitor/Pdf.php';

        $filehandler = '';

        if($this->kernel->intranet->get("pdf_header_file_id") != 0) {
            $filehandler = new FileHandler($this->kernel, $this->kernel->intranet->get("pdf_header_file_id"));
        }

        $report = new Debtor_Report_Pdf($translation, $filehandler);
        $report->visit($this);
        return $report->output($type, $filename);
    }

    function getPaymentInformation()
    {
        $info = array('bank_name'    => $this->kernel->setting->get("intranet", "bank_name"),
                      'bank_reg_number' => $this->kernel->setting->get("intranet", "bank_reg_number"),
                      'bank_account_number' => $this->kernel->setting->get("intranet", "bank_account_number"),
                      'giro_account_number' => $this->kernel->setting->get("intranet", "giro_account_number")
        );
    }


    /**
     * Til at skabe en pdf.
     *
     */
     /*
    function _pdf($type = 'stream', $filename='') {

        if($this->get('id') == 0) {
            trigger_error('Cannot create pdf from debtor without valid id', E_USER_ERROR);
        }

        $shared_pdf = $this->kernel->useShared('pdf');
        $shared_pdf->includeFile('PdfMakerDebtor.php');

        $translation = $this->kernel->getTranslation('debtor');

        $doc = new PdfMakerDebtor($this->kernel);
        $doc->start();

        if($this->kernel->intranet->get("pdf_header_file_id") != 0) {
            $filehandler = new FileHandler($this->kernel, $this->kernel->intranet->get("pdf_header_file_id"));
            // die($filehandler->get('id').' '.$filehandler->get('file_uri_pdf'));

            $doc->addHeader($filehandler->get('file_uri_pdf')); // "header.jpg"
        }


        $doc->setY('-5');

        $contact["object"] = $this->contact;
        if(strtolower(get_class($this->contact_person)) == "contactperson") {
            $contact["attention_to"] = $this->contact_person->get("name");
        }
        $intranet["address_id"] = $this->get("intranet_address_id");
        $intranet["user_id"] = $this->get("user_id");

        $docinfo[0]["label"] = $translation->get($this->get('type').' number').":";
        $docinfo[0]["value"] = $this->get("number");
        $docinfo[1]["label"] = "Dato:";
        $docinfo[1]["value"] = $this->get("dk_this_date");
        if($this->get("type") != "credit_note" && $this->get("due_date") != "0000-00-00") {
            $docinfo[2]["label"] = $translation->get($this->get('type').' due date').":";
            $docinfo[2]["value"] = $this->get("dk_due_date");
        }

        $doc->addRecieverAndSender($contact, $intranet, $translation->get($this->get('type').' title'), $docinfo);

        // Overskrifter - Vareudskrivning

        $doc->setY('-40'); // mellemrum til vareoversigt

        $apointX["varenr"] = 80;
        $apointX["tekst"] = 90;
        $apointX["antal"] = $doc->get("right_margin_position") - 150;
        $apointX["enhed"] = $doc->get('right_margin_position') - 145;
        $apointX["pris"] = $doc->get('right_margin_position') - 60;
        $apointX["beloeb"] = $doc->get('right_margin_position');
        $apointX["tekst_width"] = $doc->get('right_margin_position') - $doc->get("margin_left") - $apointX["tekst"] - 60;
        $apointX["tekst_width_small"] = $apointX["antal"] - $doc->get("margin_left") - $apointX["tekst"];


        $doc->addText($apointX["varenr"] - $doc->getTextWidth($doc->get("font_size"), "Varenr."), $doc->get('y'), $doc->get("font_size"), "Varenr.");
        $doc->addText($apointX["tekst"], $doc->get('y'), $doc->get("font_size"), "Tekst");
        $doc->addText($apointX["antal"] - $doc->getTextWidth($doc->get("font_size"), "Antal"), $doc->get('y'), $doc->get("font_size"), "Antal");
        // $doc->addText($apointX["enhed"], $doc->get('y'), $doc->get("font_size"), "Enhed");
        $doc->addText($apointX["pris"] - $doc->getTextWidth($doc->get("font_size"), "Pris"), $doc->get('y'), $doc->get("font_size"), "Pris");
        $doc->addText($apointX["beloeb"] - $doc->getTextWidth($doc->get("font_size"), "Beløb") -3, $doc->get('y'), $doc->get("font_size"), "Beløb");

        $doc->setY('-'.($doc->get("font_spacing") - $doc->get("font_size")));

        $doc->line($doc->get("margin_left"), $doc->get('y'), $doc->get('right_margin_position'), $doc->get('y'));

        // vareoversigt
        $this->loadItem();
        $items = $this->item->getList();

        $total = 0;
        if(isset($items[0]["vat"])) {
            $vat = $items[0]["vat"];
        }
        else {
            $vat = 0;
        }
        // $line_padding = 4;
        // $line_height = $doc->get("font_size") + $line_padding * 2;
        $bg_color = 0;

        for($i = 0, $max = count($items); $i <  $max; $i++) {
            $vat = $items[$i]["vat"];


            if($bg_color == 1) {
                $doc->setColor(0.8, 0.8, 0.8);
                $doc->filledRectangle($doc->get("margin_left"), $doc->get('y') - $doc->get("font_spacing"), $doc->get('right_margin_position') - $doc->get("margin_left"), $doc->get("font_spacing"));
                $doc->setColor(0, 0, 0);
            }

            $doc->setY('-'.($doc->get("font_padding_top") + $doc->get("font_size")));
            $doc->addText($apointX["varenr"] - $doc->getTextWidth($doc->get("font_size"), $items[$i]["number"]), $doc->get('y'), $doc->get("font_size"), $items[$i]["number"]);
            if($items[$i]["unit"] != "") {
                $doc->addText($apointX["antal"] - $doc->getTextWidth($doc->get("font_size"), number_format($items[$i]["quantity"], 2, ",", ".")), $doc->get('y'), $doc->get("font_size"), number_format($items[$i]["quantity"], 2, ",", "."));
                $doc->addText($apointX["enhed"], $doc->get('y'), $doc->get("font_size"), $items[$i]["unit"]);
                $doc->addText($apointX["pris"] - $doc->getTextWidth($doc->get("font_size"), number_format($items[$i]["price"], 2, ",", ".")), $doc->get('y'), $doc->get("font_size"), number_format($items[$i]["price"], 2, ",", "."));
            }
            $amount =  $items[$i]["quantity"] * $items[$i]["price"];
            $total += $amount;
            $doc->addText($apointX["beloeb"] - $doc->getTextWidth($doc->get("font_size"), number_format($amount, 2, ",", ".")), $doc->get('y'), $doc->get("font_size"), number_format($amount, 2, ",", "."));

            $tekst = $items[$i]["name"];
            $first = true;

            while($tekst != "") {

                if(!$first) {
                    $doc->setY('-'.($doc->get("font_padding_top") + $doc->get("font_size")));
                    if($bg_color == 1) {
                        $doc->setColor(0.8, 0.8, 0.8);
                        $doc->filledRectangle($doc->get("margin_left"), $doc->get('y') - $doc->get("font_spacing"), $doc->get('right_margin_position') - $doc->get("margin_left"), $doc->get("font_spacing"));
                        $doc->setColor(0, 0, 0);
                    }
                }
                $first = false;

                $tekst = $doc->addTextWrap($apointX["tekst"], $doc->get('y'), $apointX["tekst_width_small"], $doc->get("font_size"), $tekst);
                $doc->setY('-'.$doc->get("font_padding_bottom"));
                if($doc->get('y') < $doc->get("margin_bottom") + $doc->get("font_spacing") * 2) {
                    $doc->nextPage(true);
                }
            }

            if($items[$i]["description"] != "") {

                // Laver lige et mellem rum ned til teksten
                $doc->setY('-'.($doc->get("font_spacing")/2));
                if($bg_color == 1) {
                    $doc->setColor(0.8, 0.8, 0.8);
                    $doc->filledRectangle($doc->get("margin_left"), $doc->get('y'), $doc->get('right_margin_position') - $doc->get("margin_left"), $doc->get("font_spacing")/2);
                    $doc->setColor(0, 0, 0);
                }

                $desc_line = explode("\r\n", $items[$i]["description"]);
                foreach($desc_line AS $line) {
                    if($line == "") {
                        if($bg_color == 1) {
                            $doc->setColor(0.8, 0.8, 0.8);
                            $doc->filledRectangle($doc->get("margin_left"), $doc->get('y') - $doc->get("font_spacing"), $doc->get('right_margin_position') - $doc->get("margin_left"), $doc->get("font_spacing"));
                            $doc->setColor(0, 0, 0);
                        }
                        $doc->setY('-'.$doc->get("font_spacing"));
                        if($doc->get('y') < $doc->get("margin_bottom") + $doc->get("font_spacing") * 2) {
                            $doc->nextPage(true);
                        }
                    }
                    else {
                        while($line != "") {

                            if($bg_color == 1) {
                                $doc->setColor(0.8, 0.8, 0.8);
                                $doc->filledRectangle($doc->get("margin_left"), $doc->get('y') - $doc->get("font_spacing"), $doc->get('right_margin_position') - $doc->get("margin_left"), $doc->get("font_spacing"));
                                $doc->setColor(0, 0, 0);
                            }

                            $doc->setY('-'.($doc->get("font_padding_top") + $doc->get("font_size")));
                            $line = $doc->addTextWrap($apointX["tekst"], $doc->get('y') + 1, $apointX["tekst_width"], $doc->get("font_size"), $line); // Ups Ups, hvor kommer '+ 1' fra - jo ser du, ellers kappes det nederste af teksten!
                            $doc->setY('-'.$doc->get("font_padding_bottom"));

                            if($doc->get('y') < $doc->get("margin_bottom") + $doc->get("font_spacing") * 2) {
                                // print("a".$doc->get('y'));
                                $doc->nextPage(true);
                            }
                        }
                    }
                }

            }

            if($doc->get('y') < $doc->get("margin_bottom") + $doc->get("font_spacing") * 2) {
                // print("b".$doc->get('y'));
                $doc->nextPage(true);
            }

            // Hvis der har været poster med VAT, og næste post er uden, så tilskriver vi moms.
            // if($vat == 1 && $items[$i+1]["vat"] == 0) {
            if(($vat == 1 && isset($items[$i+1]["vat"]) && $items[$i+1]["vat"] == 0) || ($vat == 1 && $i+1 >= $max)) {
                // Hvis der er moms på nuværende produkt, men næste produkt ikke har moms, eller hvis vi har moms og det er sidste produkt

                ($bg_color == 1) ? $bg_color = 0 : $bg_color = 1;

                if($bg_color == 1) {
                    $doc->setColor(0.8, 0.8, 0.8);
                    $doc->filledRectangle($doc->get("margin_left"), $doc->get('y') - $doc->get("font_spacing"), $doc->get('right_margin_position') - $doc->get("margin_left"), $doc->get("font_spacing"));
                    $doc->setColor(0, 0, 0);
                }

                $doc->setLineStyle(0.5);
                $doc->line($doc->get("margin_left"), $doc->get('y'), $doc->get('right_margin_position'), $doc->get('y'));
                $doc->setY('-'.($doc->get("font_size") + $doc->get("font_padding_top")));
                $doc->addText($apointX["tekst"], $doc->get('y'), $doc->get("font_size"), "<b>25% moms af ".number_format($total, 2, ",", ".")."</b>");
                $doc->addText($apointX["beloeb"] - $doc->getTextWidth($doc->get("font_size"), "<b>".number_format($total * 0.25, 2, ",", ".")."</b>"), $doc->get('y'), $doc->get("font_size"), "<b>".number_format($total * 0.25, 2, ",", ".")."</b>");
                $total = $total * 1.25;
                $doc->setY('-'.$doc->get("font_padding_bottom"));
                $doc->line($doc->get("margin_left"), $doc->get('y'), $doc->get('right_margin_position'), $doc->get('y'));
                $doc->setLineStyle(1);
                $doc->setY('-1');
            }

            ($bg_color == 1) ? $bg_color = 0 : $bg_color = 1;
        }


        if($doc->get('y') < $doc->get("margin_bottom") + $doc->get("font_spacing") * 2) {
            // print("c".$doc->get('y'));
            $doc->nextPage();
            // print($doc->get('y'));
        }

        $doc->setLineStyle(1);
        $doc->line($doc->get("margin_left"), $doc->get('y'), $doc->get('right_margin_position'), $doc->get('y'));

        if($this->get("round_off") == 1 && $this->get("type") == "invoice" && $total != $this->get("total")) {
            $doc->setY('-'.($doc->get("font_size") + $doc->get("font_padding_top")));
            $doc->addText($apointX["enhed"], $doc->get('y'), $doc->get("font_size"), "I alt:");
            $doc->addText($apointX["beloeb"] - $doc->getTextWidth($doc->get("font_size"), number_format($total, 2, ",", ".")), $doc->get('y'), $doc->get("font_size"), number_format($total, 2, ",", "."));
            $doc->setY('-'.$doc->get("font_padding_bottom"));

            $total_text = "Total afrundet DKK:";
        }
        else {
            $total_text = "Total DKK:";
        }

        if($doc->get('y') < $doc->get("margin_bottom") + $doc->get("font_spacing") * 2) {
            // print("d".$doc->get('y'));
            $doc->nextPage(true);
        }

        $doc->setY('-'.($doc->get("font_size") + $doc->get("font_padding_top")));
        $doc->addText($apointX["enhed"], $doc->get('y'), $doc->get("font_size"), "<b>".$total_text."</b>");
        $doc->addText($apointX["beloeb"] - $doc->getTextWidth($doc->get("font_size"), "<b>".number_format($this->get("total"), 2, ",", ".")."</b>"), $doc->get('y'), $doc->get("font_size"), "<b>".number_format($this->get("total"), 2, ",", ".")."</b>");
        $doc->setY('-'.$doc->get("font_padding_bottom"));
        $doc->line($apointX["enhed"], $doc->get('y'), $doc->get('right_margin_position'), $doc->get('y'));

        // paymentcondition
        if($this->get("type") == "invoice") {

            $parameter = array(
                "contact" => $this->contact,
                "payment_text" => "Faktura ".$this->get("number"),
                "amount" => $this->get("total"),
                "payment" => $this->get('payment_total'),
                "payment_online" => $this->get('payment_online'),
                "due_date" => $this->get("dk_due_date"),
                "girocode" => $this->get("girocode"));

            $doc->addPaymentCondition($this->get("payment_method"), $parameter);
        }

        switch ($type) {
            case 'string':
                return $doc->output();
                break;
            case 'file':
                if (empty($filename)) {
                    return 0;
                }
                $data = $doc->output();
                return $doc->writeDocument($data, $filename);
                break;
            default:
                return $doc->stream();
                break;
        }
    }
    */
}

?>