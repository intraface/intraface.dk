<?php
/**
 * Debtor = debitor
 *
 * Debitorklassen bruges til både tilbud og ordrer. Den bruges fra et modul,
 * og så bygges de andre på som moduler, der benytter det overordnede modul.
 *
 * Klassen kan også bruges til at styre fakturaer.
 *
 * @package Intraface_Debtor
 * @author Lars Olesen <lars@legestue.net>
 */
class Debtor extends Intraface_Standard
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var object
     */
    public $kernel;

    /**
     * @var object
     */
    public $contact;

    /**
     * @var contact_person
     */
    public $contact_person;

    /**
     * @var array
     */
    public $value = array();

    /**
     * @var object
     */
    public $error;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var integer
     */
    protected $type_key;

    /**
     * @var object
     */
    protected $db;

    /**
     * @var object
     */
    public $dbquery;

    /**
     * @var object
     */
    public $payment;

    /**
     * Constructor
     *
     * @param object  $kernel The kernel
     * @param string  $type   @see $allowed_types
     * @param integer $id     Optional debtor id
     *
     * @return void
     */
    public function __construct($kernel, $type, $id = 0)
    {
        // sørger for at vi har det rigtige objekt
        // denne bør ikke have brug for typen.
        if (!is_object($kernel)) {
            trigger_error('Debtor kræver Kernel som objekt', E_USER_ERROR);
        }

        $this->kernel = $kernel;
        $this->type = $type;
        $this->type_key = array_search($type, Debtor::getDebtorTypes());
        if (!isset($this->type_key)) {
            trigger_error('Debtor: Ugyldig type', E_USER_ERROR);
        }

        // Her sætter vi lige type selvom den ikke er loaded, da man nogle
        // gange skal bruge type med id = 0 i getList
        $this->value["type"] = $this->type;
        $this->value["type_key"] = $this->type_key;

        // sætter variable
        $this->id = (int)$id;
        $this->db = new DB_Sql;
        $this->error = new Intraface_Error;

        $this->dbquery = new Intraface_DBQuery($this->kernel, "debtor", "debtor.active = 1 AND debtor.intranet_id = ".$this->kernel->intranet->get("id"));
        $this->dbquery->setJoin("LEFT", "contact", "debtor.contact_id = contact.id AND contact.intranet_id = ".$this->kernel->intranet->get("id"), '');
        $this->dbquery->setJoin("LEFT", "address", "address.belong_to_id = contact.id AND address.active = 1 AND address.type = 3", '');
        $this->dbquery->setJoin("LEFT", "debtor_item", "debtor_item.debtor_id = debtor.id AND debtor_item.active = 1 AND debtor_item.intranet_id = ".$this->kernel->intranet->get("id"), '');

        $this->dbquery->useErrorObject($this->error);

        if ($this->id > 0) {
            $this->load();
        }
    }

    /**
     * Creates a debtor
     *
     * TODO vi bør vende det her om og finde ud af en standard factory måde at gøre det på
     * mit forslag: factory($kernel, $type, $id)
     *
     * @param object  $kernel Kernel
     * @param integer $id     Debtor id or debtor identifier_key
     * @param type    $tpye   String TODO What is this used for as a last parameter?
     */
    public static function factory($kernel, $id = 0, $type = "")
    {
        if (is_int($id) && $id != 0) {
            $types = self::getDebtorTypes();

            $db = new DB_Sql;
            $db->query("SELECT type FROM debtor WHERE intranet_id = ".$kernel->intranet->get('id')." AND id = ".$id);
            if ($db->nextRecord()) {
                $type = $types[$db->f("type")];
            } else {
                trigger_error("Invalid id for debtor in Debtor::factory", E_USER_ERROR);
            }
        }
        elseif(is_string($id) && $id != '') {
            $types = self::getDebtorTypes();

            $db = new DB_Sql;
            $db->query("SELECT type, id FROM debtor WHERE intranet_id = ".$kernel->intranet->get('id')." AND identifier_key = \"".$id."\"");
            if ($db->nextRecord()) {
                $type = $types[$db->f("type")];
                $id = $db->f("id");
            } else {
                trigger_error("Invalid identifier_key for debtor in Debtor::factory", E_USER_ERROR);
            }
        } 

        switch ($type) {
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
                trigger_error("Ugyldig type: '".$type."'", E_USER_ERROR);
                break;
        }

        return $object;

    }

    /**
     * Loads debtor into an array
     *
     * @see $this->values;
     *
     * @return integer
     */
    public function load()
    {
        if ($this->id == 0) {
            return 0;
        }

        $this->db->query("SELECT id, number, identifier_key, intranet_address_id, contact_id, contact_address_id, contact_person_id, description, payment_method, this_date, due_date, date_stated, voucher_id, date_executed, status, where_from_id, where_from, user_id, round_off, girocode, active, message, internal_note,
                DATE_FORMAT(date_stated, '%d-%m-%Y') AS dk_date_stated,
                DATE_FORMAT(this_date, '%d-%m-%Y') AS dk_this_date,
                DATE_FORMAT(due_date, '%d-%m-%Y') AS dk_due_date,
                DATE_FORMAT(date_sent, '%d-%m-%Y') AS dk_date_sent,
                DATE_FORMAT(date_executed, '%d-%m-%Y') AS dk_date_executed,
                DATE_FORMAT(date_cancelled, '%d-%m-%Y') AS dk_date_cancelled
            FROM debtor WHERE id = ".$this->id." AND intranet_id = ".$this->kernel->intranet->get("id"));

        if (!$this->db->nextRecord()) {
            $this->error->set('Debtoren findes ikke');
            return 0;
        }
        $this->value["id"] = $this->db->f("id");
        $this->value["number"] = $this->db->f("number");
        $this->value["identifier_key"] = $this->db->f("identifier_key");
        $this->value["intranet_address_id"] = $this->db->f("intranet_address_id");
        $this->value["contact_id"] = $this->db->f("contact_id");
        $this->value["contact_address_id"] = $this->db->f("contact_address_id");
        $this->value["contact_person_id"] = $this->db->f("contact_person_id");
        $this->value["description"] = $this->db->f("description");
        if (empty($this->value["description"])) {
            $this->value["description"] = '[Ingen beskrivelse]';
        }
        $this->value["payment_method"] = $this->db->f("payment_method");
        $payment_methods = $this->getPaymentMethods();
        $this->value["translated_payment_method"] = $payment_methods[$this->db->f("payment_method")];
        $this->value["this_date"] = $this->db->f("this_date");
        $this->value["due_date"] = $this->db->f("due_date");
        $this->value["date_stated"] = $this->db->f("date_stated");
        $this->value["is_stated"] = ($this->db->f("date_stated") > '0000-00-00');
        $this->value["voucher_id"] = $this->db->f("voucher_id");
        $this->value["dk_date_stated"] = $this->db->f("dk_date_stated");
        $this->value["dk_this_date"] = $this->db->f("dk_this_date");
        $this->value["dk_due_date"] = $this->db->f("dk_due_date");
        $this->value["dk_date_sent"] = $this->db->f("dk_date_sent");
        $this->value["date_executed"] = $this->db->f("date_executed");
        $this->value["dk_date_executed"] = $this->db->f("dk_date_executed");
        $this->value["dk_date_cancelled"] = $this->db->f("dk_date_cancelled");
        $status_types = $this->getStatusTypes();
        $this->value["status"] = $status_types[$this->db->f("status")];
        $this->value["status_id"] = $this->db->f("status");
        // $this->value["is_credited"] = $this->db->f("is_credited");
        $froms = $this->getFromTypes();
        $this->value["where_from"] = $froms[$this->db->f("where_from")];
        $this->value["where_from_id"] = $this->db->f("where_from_id");
        $this->value["user_id"] = $this->db->f("user_id");
        $this->value["round_off"] = $this->db->f("round_off");
        $this->value["girocode"] = $this->db->f("girocode");
        $this->value["message"] = $this->db->f("message");
        $this->value["internal_note"] = $this->db->f("internal_note");
        $this->value["active"] = $this->db->f("active");

        // Bruges til at afgøre, hvor debtor er sendt hent til
        $db = new DB_Sql;
        $db->query("SELECT id, type FROM debtor WHERE where_from_id = " . $this->id . " AND active = 1");
        if ($db->nextRecord()) {

            if ($db->f('type') > 0) {
                $types = Debtor::getDebtorTypes();
                $this->value['where_to'] = $types[$db->f('type')];
            } else {
                $this->value['where_to'] = '';
            }
            $this->value['where_to_id'] = $db->f('id');
        } else {
            $this->value['where_to'] = '';
            $this->value['where_to_id'] = 0;
        }

        if ($this->get("status") == "executed" || $this->get("status") == "cancelled") {
            $this->value["locked"] = true;
        } else {
            $this->value["locked"] = false;
        }

        // henter kunden
        $this->contact = new Contact($this->kernel, $this->db->f("contact_id"), $this->db->f("contact_address_id"));
        if ($this->contact->get("type") == "corporation" && $this->db->f("contact_person_id") != 0) {
            $this->contact_person = new ContactPerson($this->contact, $this->db->f("contact_person_id"));
        }

        // henter items på debtoren
        $this->loadItem();
        $item = $this->item->getList();
        $this->value['items'] = $item;

        for ($i = 0, $max = count($item), $total = 0; $i<$max; $i++) {
            $total += $item[$i]["amount"];
        }

        if ($this->get("round_off") == 1 && $this->get("type") == "invoice") {
            $decimal = $total - floor($total);
            $decimal *= 4;
            $decimal = round($decimal)/4;
            $total = $decimal + floor($total);
        }

        $this->value["total"] = round($total, 2);
        $this->value['payment_total'] = 0;

        if ($this->value["type"] == "invoice") {
            foreach ($this->getDebtorAccount()->getList() AS $payment) {
                $this->value['payment_total'] += $payment["amount"];
            }
        }

        $this->value['arrears'] = $this->value['total'] - $this->value['payment_total'];

        return true;
    }

    /**
     * update()
     *
     * @param array   $input
     * @param string  $from    Bruges til at fortælle, hvor debtoren kommer fra, fx webshop eller quotation
     * @param integer $from_id Hvis debtoren kommer fra en anden debtor.
     */
    public function update($input, $from = 'manuel', $from_id = 0)
    {
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
        $validator = new Intraface_Validator($this->error);

        // nummeret
        if (empty($input["number"])) {
            $input["number"] = $this->getMaxNumber() + 1;
        }
        $validator->isNumeric($input["number"], "Nummeret (".$input["number"].")  skal være et tal", "greater_than_zero");
        if (!$this->isNumberFree($input["number"])) {
            $this->error->set("Nummeret er allerede benyttet");
        }

        // kunde
        $validator->isNumeric($input["contact_id"], "Du skal angive en kunde", "greater_than_zero");
        $contact = new Contact($this->kernel, $input["contact_id"]);
        if (is_object($contact->address)) {
            $contact_address_id = $contact->address->get("address_id");
        }
        else {
            $this->error->set("Ugyldig kunde");
        }

        if ($contact->get("type") == "corporation") {
            $validator->isNumeric($input["contact_person_id"], "Der er ikke angivet en kontaktperson");
        }
        else {
            $input["contact_person_id"] = 0;
        }
        $validator->isString($input['description'], 'Fejl i beskrivelse', '', 'allow_empty');

        if ($validator->isDate($input["this_date"], "Ugyldig dato", "allow_no_year")) {
            $this_date = new Intraface_Date($input["this_date"]);
            $this_date->convert2db();
        }
        if ($this->type == "invoice") {
          // Hvis det er en faktura skal der indtastes en due_date, ellers er det ligegyldigt!
            if ($validator->isDate($input["due_date"], "Ugyldig leveringsdato", "allow_no_year")) {
                $due_date = new Intraface_Date($input["due_date"]);
                $due_date->convert2db();
                $due_date_db = $due_date->get();
            }
        } else {
            if ($validator->isDate($input["due_date"], "Ugyldig leveringsdato", "allow_no_year,allow_empty")) {
                // der skal laves en due-date, som bare bliver dags datoe, hvis ikke der er indtastet nogen.
                if (!empty($input["due_date"])) {
                    $due_date = new Intraface_Date($input["due_date"]);
                    $due_date->convert2db();
                    $due_date_db = $due_date->get();
                } else {
                    $due_date_db = date('Y-m-d');
                }
            }
        }

        settype($input['payment_method'], 'integer');
        settype($input['girocode'], 'string');
        $validator->isString($input['girocode'], 'error in girocode', '', 'allow_empty');

        settype($input['message'], 'string');
        $validator->isString($input['message'], 'error in message', '', 'allow_empty');

        $internal_note_sql = '';
        if (isset($input['internal_note'])) {
            $internal_note_sql = ", internal_note = '".$input['internal_note']."'";
        }


        if (isset($input["round_off"]) && intval($input["round_off"])) {
            $input["round_off"] = 1;
        } else {
            $input["round_off"] = 0;
        }
        
        if ($this->error->isError()) {
            return 0;
        }
      // user_id = ".$this->kernel->user->get('id').", // skal puttes på, men kun hvis det ikke er fra webshop.
        $db = new DB_Sql;
        if ($this->id == 0) {
            
            $infinite_check = 0;
            do {
                $random = new Ilib_RandomKeyGenerator();
                $identifier = $random->generate(30);
                $db->query('SELECT id FROM debtor WHERE identifier_key = "'.$identifier.'" AND intranet_id = '.$this->kernel->intranet->get('id'));
                $infinite_check++;
                if($infinite_check > 20) {
                    throw new Exception('Unable to generate an unique key');
                }
            } while ($db->nextRecord());
            
            $sql_type = "INSERT INTO ";
            $sql_after = ", date_created = NOW(), identifier_key = \"".$identifier."\", intranet_id = " . $this->kernel->intranet->get('id');
        } else {
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

        if ($this->id == 0) {
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
     * Må kun bruges hvis hele ordren skal slettes
     *
     * @return boolean
     */
    public function delete()
    {
        if ($this->id > 0 AND $this->get("locked") == true) {
            $this->error->set('Posten er låst og kan ikke slettes');
            return false;
        }
        $db = new DB_Sql;
        $db->query("SELECT id FROM debtor WHERE id = " . $this->id . " AND intranet_id = " . $this->kernel->intranet->get('id'). " AND type = '".$this->type_key."' LIMIT 1");
        if ($db->nextRecord()) {
            $db->query("UPDATE debtor SET active = 0 WHERE id = " . $this->id . " AND intranet_id = " . $this->kernel->intranet->get('id') . "  AND type = '".$this->type_key."'");
            return true;
        }
        return false;
    }

    /**
     * Funktionen bruges til at gemme et debitorobjekt som et andet.
     * Denne funktion er nyttig, når jeg fx skal transformere et tilbud til en ordre. Så smider
     * jeg bare et objekt med tilbuddet ind i et nyt objekt jeg skaber med ordre, og så går
     * det hele automatisk.
     *
     * TODO Kunne være vakst om den lige gav det nye objekt en beskrivelse.
     *
     * <code>
     * $quot_id = 10;
     * $quot = new Debtor($kernel, 'quotation', $quot_id);
     * $order = new Debtor($kernel, 'order');
     * $order->create($quot);
     * </code>
     *
     * @param $object $debtor_object Debtor object
     *
     * @return int = id på den nye debtor, der skabes.
     */
    public function create($debtor_object)
    {
        if (!is_object($debtor_object)) {
            trigger_error('Debtor: create() har brug for et debtor-objekt, jeg kan skabe et nyt debtorobjekt med', E_USER_ERROR);
        }

        if ($debtor_object->get("type") == "invoice") {
            // Faktura kan godt krediteres selvom den er låst.
            if ($debtor_object->get("status") == "created" || $debtor_object->get("status") == "cancelled") {
                $this->error->set('Debtor::Created kan ikke lave kreditnota fra faktura, når fakturaen ikke er sendt eller færdigbehandlet', E_USER_ERROR);
                return false;
            }
        }
        else {
            if ($debtor_object->get('locked') == true) {
                $this->error->set('Objektet er låst, så du kan ikke lave et nyt objekt fra det.', E_USER_ERROR);
                return false;
            }
        }

        $values = $debtor_object->get();
        $values['this_date'] = date('d-m-Y');
        $values['number'] = ''; // nulstiller nummeret ellers vil den få samme nummer

        switch ($this->type) {
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

        if ($new_debtor_id = $this->update($values, $debtor_object->get("type"), $debtor_object->get('id'))) {
            $debtor_object->loadItem();
            $items = $debtor_object->item->getList();

            foreach ($items AS $item) {
                $this->loadItem();
                $debtor_object->loadItem($item['id']);
                $item_values = $debtor_object->item->get();
                $item_values["quantity"] = number_format($item_values["quantity"], 2, ",", "");
                $this->item->save($item_values);
            }

            if ($this->type != "credit_note") {
                // Hvis det er en credit_note, så skal fakturanet ikke låses, da man ikke ved om kreditnotaen er på hele fakturaen
                $debtor_object->setStatus('executed');
            }


            // Overførsel af onlinebetaling fra ordre til faktura.
            if ($debtor_object->get('type') == "order" && $this->kernel->intranet->hasModuleAccess('onlinepayment')) {
                $onlinepayment_module = $this->kernel->useModule('onlinepayment', true); // true: ignore user permisssion
                $onlinepayment = OnlinePayment::factory($this->kernel);

                $onlinepayment->dbquery->setFilter('belong_to', 'order');
                $onlinepayment->dbquery->setFilter('belong_to_id', $debtor_object->get('id'));
                $payment_list = $onlinepayment->getlist();

                foreach ($payment_list AS $p) {
                    $tmp_onlinepayment = OnlinePayment::factory($this->kernel, 'id', $p['id']);
                    $tmp_onlinepayment->changeBelongTo('invoice', $new_debtor_id);
                }
            }



            return $new_debtor_id;
        }
        return 0;
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Sætter status for debtoren
     *
     * @return true / false
     */
    public function setStatus($status)
    {
        if (is_string($status)) {
            $status_id = array_search($status, $this->getStatusTypes());
            if ($status_id === false) {
                trigger_error("Debtor->setStatus(): Ugyldig status (streng)", E_USER_ERROR);
            }
        } else{
            $status_id = intval($status);
            $status_types = $this->getStatusTypes();
            if (isset($status_types[$status_id])) {
                $status = $status_types[$status];
            } else {
                trigger_error("Debtor->setStatus(): Ugyldig status (integer)", E_USER_ERROR);
            }
        }

        if ($status_id == $this->get("status_id")) {
            trigger_error("Du kan ikke sætte status til samme som den er i forvejen", E_USER_ERROR);
        }
        if (($this->get("type") != "invoice" && $status_id < $this->get("status_id")) || ($this->get("type") == "invoice" && $this->get("status") != "executed" && $status_id < $this->get("status_id"))) {
            // Man kan godt gå fra executed til sent, hvis f.eks. en betalt faktura bliver efterfølgende bliver krediteret
            trigger_error("Du kan ikke sætte status lavere end den er i forvejen", E_USER_ERROR);
        }

        switch ($status) {
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
     *  fx fra en webshop, eller hvor de bliver skabt fra et andet sted, fx en ordre skabt fra et tilbud-
     *
     * @param string  $from    Where from
     * @param integer $from_id From id
     *
     * @return true / false
     */
    private function setFrom($from = 'manuel', $from_id = 0)
    {
        $from = array_search($from, $this->getFromTypes());
        if ($from === false) {
            trigger_error('Debtor->setFrom(): Ugyldig from', E_USER_ERROR);
        }
        $from_id = (int)$from_id;

        if ($this->error->isError()) {
            return false;
        }
        $db = new Db_Sql;
        $db->query("UPDATE debtor SET where_from = ".(int)$from.", where_from_id = " . $from_id . " WHERE id = " . $this->id);
        return true;
    }

    /**
     * setNewContact();
     * Bruges til at skifte kunden på en debtor, er fx nyttig ved webshopordrene, hvis
     * kunden allerede findes i systemet.
     * Måske den burde indholde en kontrol af at kunden overhovedet findes og tilhøre dette intranet /Sune (21/3 2005)
     *
     * @param $contact_id int
     *
     * @return boolean
     */
    public function setNewContact($contact_id)
    {
        if ($this->id == 0) {
            return false;
        }
        $contact_id = (int)$contact_id;
        $db = new DB_Sql;
        $db->query("UPDATE debtor SET contact_id = " . $contact_id . " WHERE id = " . $this->id . " AND intranet_id = " . $this->kernel->intranet->get("id") . " AND type='".$this->type_key."'");

        return true;
    }

    //////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Bruges til at lave en menu på kontakten eller produktet
     *
     * @param string  $type    contact eller product
     * @param integer $type_id id på contact eller product.
     *
     * @return integer
     */
    public function any($type, $type_id)
    {
        switch ($type) {
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
     * Funktion til at finde ud af, om der er oprettet nogen poster af den aktuelle bruger
     *
     * @return integer
     */
    public function isFilledIn()
    {
        $db = new DB_Sql;
        $db->query("SELECT id FROM debtor WHERE type = " . $this->type_key . " AND intranet_id = " . $this->kernel->intranet->get('id'));
        return $db->numRows();
    }

    /**
     * Gets a list with debtors
     *
     * @return array
     */
    public function getList()
    {
        $db = new DB_Sql;

        $this->dbquery->setCondition("debtor.type = ".$this->get("type_key"));

        if ($this->dbquery->checkFilter("contact_id")) {
            $this->dbquery->setCondition("debtor.contact_id = ".intval($this->dbquery->getFilter("contact_id")));
        }

        if ($this->dbquery->checkFilter("text")) {
            $this->dbquery->setCondition("(debtor.description LIKE \"%".$this->dbquery->getFilter("text")."%\" OR debtor.girocode = \"".$this->dbquery->getFilter("text")."\" OR debtor.number = \"".$this->dbquery->getFilter("text")."\" OR address.name LIKE \"%".$this->dbquery->getFilter("text")."%\")");
        }

        if ($this->dbquery->checkFilter("from_date")) {
            $date = new Intraface_Date($this->dbquery->getFilter("from_date"));
            if ($date->convert2db()) {
                $this->dbquery->setCondition("debtor.this_date >= \"".$date->get()."\"");
            } else {
                $this->error->set("Fra dato er ikke gyldig");
            }
        }

        if ($this->dbquery->checkFilter("product_id")) {
            $this->dbquery->setCondition("debtor_item.product_id = ".$this->dbquery->getFilter('product_id'));
        }


        // Poster med fakturadato før slutdato.
        if ($this->dbquery->checkFilter("to_date")) {
            $date = new Intraface_Date($this->dbquery->getFilter("to_date"));
            if ($date->convert2db()) {
                $this->dbquery->setCondition("debtor.this_date <= \"".$date->get()."\"");
            } else {
                $this->error->set("Til dato er ikke gyldig");
            }
        }
        // alle ikke bogførte skal findes
        if ($this->dbquery->checkFilter("not_stated")) {
            $this->dbquery->setCondition("voucher_id = 0");

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
                        $this->dbquery->setCondition("(debtor.date_executed >= \"".$date->get()."\" AND debtor.status = 2) OR (debtor.date_cancelled >= \"".$date->get()."\") OR debtor.status < 2");
                    }
                } else {
                    // Hvis der ikke er nogen dato så tager vi alle dem som på nuværende tidspunkt har status under
                    $this->dbquery->setCondition("debtor.status < 2");
                }

            } elseif ($this->dbquery->getFilter("status") == "-3") {
                //  Afskrevne. Vi tager først alle sendte og executed.

                if ($this->get("type") != "invoice") {
                    trigger_error("Afskrevne kan kun benyttes ved faktura", E_USER_ERROR);
                }

                $this->dbquery->setJoin("INNER", "invoice_payment", "invoice_payment.payment_for_id = debtor.id", "invoice_payment.intranet_id = ".$this->kernel->intranet->get("id")." AND invoice_payment.payment_for = 1");
                $this->dbquery->setCondition("invoice_payment.type = -1");

                if ($this->dbquery->checkFilter("to_date")) {
                    $date = new Intraface_Date($this->dbquery->getFilter("to_date"));
                    if ($date->convert2db()) {
                        // alle som er sendte på datoen og som ikke er cancelled
                        $this->dbquery->setCondition("debtor.date_sent <= '".$date->get()."' AND debtor.status != 3");
                        $this->dbquery->setCondition("invoice_payment.payment_date <= '".$date->get()."'");
                    }
                } else {
                    // Hvis der ikke er nogen dato så tager vi alle dem som på nuværende tidspunkt har status under
                    $this->dbquery->setCondition("status = 1 OR status = 2");
                }
            } else {
                switch ($this->dbquery->getFilter("status")) {
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

                if ($this->dbquery->checkFilter("to_date")) {
                    $date = new Intraface_Date($this->dbquery->getFilter("to_date"));
                    if ($date->convert2db()) {
                        $this->dbquery->setCondition("debtor.".$to_date_field." <= \"".$date->get()."\"");
                    }
                } else {
                    // tager dem som på nuværende tidspunkt har den angivet status
                    $this->dbquery->setCondition("debtor.status = ".intval($this->dbquery->getFilter("status")));
                }
            }
        }

        switch ($this->dbquery->getFilter("sorting")) {
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

            $debtor = Debtor::factory($this->kernel, (int)$db->f("id"));
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

            $i++;

        }
        return $list;
    }

    /**
     * Checks whether a product number is available
     *
     * @return boolean
     */
    private function isNumberFree($number)
    {
        $number = safeToDb($number);
        $db = new DB_Sql;
        $sql = "SELECT id FROM debtor WHERE intranet_id = " . $this->kernel->intranet->get('id') . " AND number = '".$number."' AND type = '".$this->type_key."' AND id != " . $this->id." AND active = 1";
        $db->query($sql);
        if ($db->numRows() == 0) {
            return true;
        }
        return false;
    }

    /**
     * Gets the max number
     *
     * @return integer
     */
    public function getMaxNumber()
    {
        $db = new DB_Sql;
        $db->query("SELECT MAX(number) AS max_number FROM debtor WHERE intranet_id = ".$this->kernel->intranet->get("id")." AND type=".$this->type_key." AND active = 1");
        if ($db->nextRecord()) {
            return $db->f('max_number');
        }
        return 0;
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Loads the items
     *
     * @return void
     */
    public function loadItem($id = 0)
    {
        require_once 'DebtorItem.php';
        $this->item = new DebtorItem($this, (int)$id);
    }

    /**
     * Gets all items
     *
     * @return array
     */
    public function getItems()
    {
        $this->loadItem();
        return $this->item->getList();
    }

    /**
     * Adds an item
     *
     * @return void
     */
    public function addItem()
    {
        // TODO hide the implementation details of how to add an item. Also to conform with basket.
    }

    /* FUNKTIONER TIL BOGFØRING ***********************************************************/

    /**
     * Dummy method to checks whether the debtor can be stated
     * This method is overwritten in the extended class.
     *
     * @return boolean
     */
    public function readyForState()
    {
        $this->error->set('Denne type kan ikke bogføres');
        return false;

    }

    /**
     * Set the debtor as stated
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
        $db->query("UPDATE debtor SET date_stated = '" . $voucher_date . "', voucher_id = '".$voucher_id."' WHERE id = " . $this->id . " AND intranet_id = " . $this->kernel->intranet->get('id'));
        return true;
    }

    /**
     * Check whether debtor has been stated
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

    /////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Gets invoice text
     *
     * @return string
     */
    public function getInvoiceText()
    {
        return $this->kernel->setting->get('intranet', 'debtor.invoice.text');
    }

    /**
     * Gets contact information
     *
     * @return array
     */
    public function getContactInformation()
    {
        $intranet = array();
        switch ($this->kernel->setting->get('intranet', 'debtor.sender')) {
            case 'intranet':
                // void
                break;
            case 'user':
                // @todo needs to be changed in order to work with contactlogin
                $intranet['email'] = $this->kernel->user->getAddress()->get('email');
                $intranet['contact_person'] = $this->kernel->user->getAddress()->get('name');
                $intranet['phone'] = $this->kernel->user->getAddress()->get('phone');
                break;
            case 'defined':
                $intranet['email'] = $this->kernel->setting->get('intranet', 'debtor.sender.email');
                $intranet['contact_person'] = $this->kernel->setting->get('intranet', 'debtor.sender.name');
                break;
        }

        return $intranet;
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

    /**
     * Gets the intranet address
     *
     * @return object
     */
    public function getIntranetAddress()
    {
        return new Intraface_Address($this->get("intranet_address_id"));
    }

    function getContact()
    {
        return new Contact($this->kernel, $this->get("contact_id"), $this->get("contact_address_id"));
    }

    /**
     * returns the possible debtor types!
     *
     * @return array types
     */
    static function getDebtorTypes()
    {
        return array(
            1=>'quotation',
            2=>'order',
            3=>'invoice',
            4=>'credit_note');
    }

    /**
     * returns the possible places where the debtor comes from
     *
     * @return array with the allowed froms
     */
    private function getFromTypes()
    {
        return array(
            1=>'manuel',
            2=>'webshop',
            3=>'quotation',
            4=>'order',
            5=>'invoice'
        );
    }

    /**
     * returns possible status types
     *
     * @return array possible status types
     */
    private function getStatusTypes()
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
    private function getPaymentMethods()
    {
        return array(
            0=>'Ingen',
            1=>'Kontooverførsel',
            2=>'Girokort +01',
            3=>'Girokort +71'
        );
    }
}
?>