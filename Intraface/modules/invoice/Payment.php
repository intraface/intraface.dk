<?php
/**
 * Klasse bruges til at registere betaling af faktura, og rykker(gebyr)
 * @package Intraface_Invoice
 *
 * @author Sune Jensen <sj@sunet.dk>
 * @version: 1
 */

Class Payment extends Standard {

    var $id;
    var $kernel;
    var $payment_for;
    var $payment_for_type_id;
    var $payment_for_id;
    var $error;
    var $dbquery;
    var $types;

    function __construct($object, $id = 0) {

        $object_class = strtolower(get_class($object));

        if($object_class == "kernel") {
            $this->kernel      = $object;
            $this->payment_for = "";
            $this->error       = new Error;

            $this->payment_for_type_id = 0;
            $this->payment_for_id = 0;
        } elseif($object_class == "invoice" || $object_class == "reminder") {
            $this->kernel      = $object->kernel;
            $this->payment_for = $object;
            $this->error       = $object->error;

            $module = $this->kernel->getModule("invoice");
            if($object_class == "invoice") {
                $this->payment_for_type_id = array_search("invoice", $module->getSetting("payment_for"));
            } else {
                $this->payment_for_type_id = array_search("reminder", $module->getSetting("payment_for"));
            }
            $this->payment_for_id = $this->payment_for->get("id");


        } else {
            trigger_error("Ugyldig object som første parameter. Det skal være Kernel, Invoice eller CreditNote", E_USER_ERROR);
        }
        $this->id = intval($id);

        $invoice_module = $this->kernel->getModule("invoice");
        $this->types = $invoice_module->getSetting("payment_type");

        $this->dbquery = new DBQuery($this->kernel, "invoice_payment", "intranet_id = ".$this->kernel->intranet->get("id")." AND payment_for = ".$this->payment_for_type_id." AND payment_for_id = ".$this->payment_for_id);
        $this->dbquery->useErrorObject($this->error);

        if($this->id != 0) {
            $this->load();
        }
    }

    function update($input = "")
    {
        $already_paid = 0;
        $value = $this->getList();
        for($i = 0, $max = count($value); $i < $max; $i++) {
            $already_paid += $value[$i]["amount"];
        }
        $payment_for_total = $this->payment_for->get("total");

        if(is_array($input)) {
            // Man har mulighed for at køre $payment->update() bare for at få den til at
            // sætte invoice eller reminder til executed

            $input = safeToDb($input);

            $validator = new Validator($this->error);

            if($validator->isDate($input["payment_date"], "Ugyldig dato", "allow_no_year")) {
                $date = new Intraface_Date($input["payment_date"]);
                $date->convert2db();
            }

            if($validator->isDouble($input["amount"], "Ugyldig beløb")) {
                $amount = new Amount($input["amount"]);
                $amount->convert2db();
                $amount = $amount->get();
            }

            if (array_key_exists('description', $input)) {
                $validator->isString($input["description"], "Fejl i beskrivelse", "", "allow_empty");
            } else {
                $input['description'] = '';
            }

            $validator->isNumeric($input["type"], "Type er ikke angivet korrekt");
            settype($input["type"], "integer");
            if(!isset($this->types[$input["type"]])) {
                $this->error->set("Ugyldig type");
            }


            // Hermed lovliggjort at registrere større beløb en betalingen. Det er ikke til at kontrollere med kreditnota.
            /*
            if($amount + $already_paid > $payment_for_total) {
                $this->error->set("Beløb er større end det skyldige beløb");
            }
            */

            if($this->error->isError()) {
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
        }

        if(is_object($this->payment_for)) {
            $this->payment_for->updateStatus();
        }
        return true;
    }

    function getList()
    {

        $db = new DB_sql;
        $value = array(); // type(invoice,payment,credit_note,reminder), description, date, amount
        $i = 0;
        $payment = array();
        $credit_note = array();
        $invoice_module = $this->kernel->getModule("invoice");

        // Hent betalinger
        if(is_object($this->payment_for)) {

            if($this->dbquery->checkFilter("to_date")) {
                $date = new Intraface_Date($this->dbquery->getFilter("to_date"));
                if($date->convert2db()) {
                    $this->dbquery->setCondition("payment_date <= \"".$date->get()."\"");
                }
            }

            $this->dbquery->setSorting("payment_date ASC");
            $db = $this->dbquery->getRecordset("id, amount, type, description, payment_date, payment_for_id, DATE_FORMAT(payment_date, '%d-%m-%Y') AS dk_payment_date", "", false);
            while($db->nextRecord()) {
                $payment[$i]["id"] = $db->f("id");
                if($db->f("type") == -1) {

                    $payment[$i]["type"] = "depriciation";
                    //$payment[$i]["dk_type"] = $invoice_module->getTranslation("deprication");
                    $payment[$i]["amount"] = $db->f("amount");
                    $payment[$i]["description"] = $db->f("description");
                } else {
                    $payment[$i]["type"] = $this->types[$db->f('type')];
                    // $payment[$i]["type"] = "payment";
                    //$payment[$i]["dk_type"] = $invoice_module->getTranslation("payment");
                    $payment[$i]["amount"] = $db->f("amount");
                    //$payment[$i]["description"] = "(".$invoice_module->getTranslation($this->types[$db->f("type")]).") ".$db->f("description");
                    $payment[$i]["description"] = $db->f("description");
                }
                $payment[$i]["payment_date"] = $db->f("payment_date");
                $payment[$i]["dk_payment_date"] = $db->f("dk_payment_date");

                // $payment[$i]["payment_for"] = $this->payment_for[$db->f("payment_for")];
                $payment[$i]["payment_for_id"] = $db->f("payment_for_id");

                $i++;
            }
        }


        // Hent kreditnotaer. Ikke hvis det er en reminder. Den kan ikke krediteres.
        if(strtolower(get_class($this->payment_for)) !== "reminder") {
            $debtor = new CreditNote($this->kernel);
            // Hvis det er en faktura
            if(strtolower(get_class($this->payment_for)) == "invoice") {
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

            if(isset($payment[$pay]["payment_date"]) && $payment[$pay]["payment_date"] != "") {
                $pay_date = strtotime($payment[$pay]["payment_date"]);
            } else {
                $pay_date = 0;
            }

            if(isset($credit_note[$cre]['this_date']) && $credit_note[$cre]["this_date"] != "") {
                $cre_date = strtotime($credit_note[$cre]["this_date"]);
            } else {
                $cre_date = 0;
            }

            if($pay_date != 0) {
                $next = "payment";
            } elseif($cre_date != 0) {
                $next = "credit_note";
            }

            if($cre_date != 0 && $cre_date < $pay_date) $next = "credit_note";

            if($next == "payment") {

                $value[$i]["type"] = $payment[$pay]["type"];
                //$value[$i]["dk_type"] = $payment[$pay]["dk_type"];
                $value[$i]["id"] = $payment[$pay]["id"];
                $value[$i]["date"] = $payment[$pay]["payment_date"];
                $value[$i]["dk_date"] = $payment[$pay]["dk_payment_date"];
                $value[$i]["description"] = $payment[$pay]["description"];
                $value[$i]["amount"] = $payment[$pay]["amount"];
                $pay++;
            } elseif($next == "credit_note") {
                $value[$i]["type"] = "credit_note";
                //$value[$i]["dk_type"] = $invoice_module->getTranslation("credit_note");
                $value[$i]["id"] = $credit_note[$cre]["id"];
                $value[$i]["date"] = $credit_note[$cre]["this_date"];
                $value[$i]["dk_date"] = $credit_note[$cre]["dk_this_date"];
                if($credit_note[$cre]["description"] != "") {
                    $value[$i]["description"] = $credit_note[$cre]["description"];
                }
                else {
                    $value[$i]["description"] = "[Ingen beskrivelse]";
                }
                $value[$i]["amount"] = $credit_note[$cre]["total"];
                $cre++;
            }

            $i++;
        }

        return $value;
    }
}

?>