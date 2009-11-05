<?php
class Intraface_modules_procurement_ProcurementGateway
{
    protected $dbquery;
    protected $error;
    protected $kernel;

    function __construct($kernel)
    {
        $this->kernel = $kernel;
        $this->error = new Intraface_Error;
        $this->dbquery = $this->getDBQuery();

    }

    function getDBQuery()
    {
        $this->dbquery = new Intraface_DBQuery($this->kernel, "procurement", "active = 1 AND intranet_id = ".$this->kernel->intranet->get("id"));
        return $this->dbquery->useErrorObject($this->error);
    }

    function getByContactId($id)
    {
        $this->getDBQuery()->setFilter('contact_id', $id);
        $this->getDBQuery()->setCondition('newsletter_subscriber.contact_id = '.$this->getDBQuery()->getFilter('contact_id'));

        return $this->dbquery->getRecordset("*,
            DATE_FORMAT(invoice_date, '%d-%m-%Y') AS dk_invoice_date,
            DATE_FORMAT(delivery_date, '%d-%m-%Y') AS dk_delivery_date,
            DATE_FORMAT(payment_date, '%d-%m-%Y') AS dk_payment_date,
            DATE_FORMAT(paid_date, '%d-%m-%Y') AS dk_paid_date");
    }

    function setNewContactId($old_id, $new_id)
    {
        $db = MDB2::singleton();
        $db->query('UPDATE procurement SET contact_id = ' . $new_id . ' WHERE contact_id = ' . $old_id);
    }


    function getList()
    {
        $list = array();

        if ($this->dbquery->checkFilter("contact_id")) {
            $this->dbquery->setCondition("contact_id = ".intval($this->dbquery->getFilter("contact_id")));
        }

        if ($this->dbquery->checkFilter("text")) {
            $this->dbquery->setCondition("(description LIKE \"%".$this->dbquery->getFilter("text")."%\" OR number = \"".$this->dbquery->getFilter("text")."\")");
        }

        if ($this->dbquery->checkFilter("from_date")) {
            $date = new Intraface_Date($this->dbquery->getFilter("from_date"));
            if ($date->convert2db()) {
                $this->dbquery->setCondition("invoice_date >= \"".$date->get()."\"");
            } else {
                $this->error->set("Fra dato er ikke gyldig");
            }
        }

        // Poster med fakturadato før slutdato.
        if ($this->dbquery->checkFilter("to_date")) {
            $date = new Intraface_Date($this->dbquery->getFilter("to_date"));
            if ($date->convert2db()) {
                $this->dbquery->setCondition("invoice_date <= \"".$date->get()."\"");
            } else {
                $this->error->set("Til dato er ikke gyldig");
            }
        }

        if ($this->dbquery->checkFilter("status")) {
            if ($this->dbquery->getFilter("status") == "-1") {
                // Behøves ikke, den tager alle.

            } elseif ($this->dbquery->getFilter("status") == "-2") {
                // Not executed = åbne
                /*
                if ($this->dbquery->checkFilter("to_date")) {
                    $date = new Intraface_Date($this->dbquery->getFilter("to_date"));
                    if ($date->convert2db()) {
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

            } else {
                if ($this->dbquery->checkFilter("to_date")) {
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
                    if ($date->convert2db()) {
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
            DATE_FORMAT(invoice_date, '%d-%m-%Y') AS dk_invoice_date,
            DATE_FORMAT(delivery_date, '%d-%m-%Y') AS dk_delivery_date,
            DATE_FORMAT(payment_date, '%d-%m-%Y') AS dk_payment_date,
            DATE_FORMAT(paid_date, '%d-%m-%Y') AS dk_paid_date");

        $status_types = $this->getStatusTypes();
        while ($db->nextRecord()) {
            $list[$i]["id"] = $db->f("id");
            $list[$i]["description"] = $db->f("description");
            $list[$i]["number"] = $db->f("number");
            $list[$i]["vendor"] = $db->f("vendor");
            $list[$i]["status_key"] = $db->f("status_key");

            $list[$i]["status"] = $status_types[$db->f("status_key")];
            $list[$i]["delivery_date"] = $db->f("delivery_date");
            $list[$i]["dk_delivery_date"] = $db->f("dk_delivery_date");
            $list[$i]["dk_invoice_date"] = $db->f("dk_invoice_date");
            $list[$i]["payment_date"] = $db->f("payment_date");
            $list[$i]["dk_payment_date"] = $db->f("dk_payment_date");
            $list[$i]["paid_date"] = $db->f("paid_date");
            $list[$i]["dk_paid_date"] = $db->f("dk_paid_date");
            $list[$i]["contact_id"] = $db->f("contact_id");
            $list[$i]["total_price"] = round($db->f("price_items") + $db->f("price_shipment_etc") + $db->f("vat"), 2);;

            $i++;
        }

        return $list;
    }

}