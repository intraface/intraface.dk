<?php
class Intraface_modules_invoice_ReminderGateway
{
    protected $kernel;
    protected $dbquery;
    protected $db;
    protected $error;

    function __construct($kernel)
    {
        $this->kernel = $kernel;
        $this->error = new Intraface_Error;
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

    function findById($id)
    {
        return new Reminder($this->kernel, $id);
    }

    /**
     * Bruges ift. kontakter
     */
    function findCountByContactId($contact_id)
    {
        $contact_id = (int)$contact_id;
        if ($contact_id == 0) {
            return 0;
        }
        $db = new DB_Sql;
        $db->query("SELECT id
            FROM invoice_reminder WHERE intranet_id = ".$this->kernel->intranet->get("id")." AND active = 1 AND contact_id=" . $contact_id);
        return $db->numRows();
    }

    function setNewContactId($old_contact_id, $new_contact_id)
    {
        $db = new DB_Sql;
        $db->query('UPDATE invoice_reminder SET contact_id = ' . $new_contact_id . ' WHERE contact_id = ' . $old_contact_id);
        return true;
    }

    function findAll()
    {
        $this->getDBQuery();

        $this->dbquery->setSorting("number DESC, this_date DESC");
        $i = 0;

        if ($this->dbquery->checkFilter("contact_id")) {
            $this->dbquery->setCondition("contact_id = ".intval($this->dbquery->getFilter("contact_id")));
        }

        if ($this->dbquery->checkFilter("invoice_id")) {
            $this->dbquery->setCondition("invoice_id = ".intval($this->dbquery->getFilter("invoice_id")));
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

        // Poster med fakturadato f�r slutdato.
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
                // Beh�ves ikke, den tager alle.
                // $this->dbquery->setCondition("status >= 0");
            } elseif ($this->dbquery->getFilter("status") == "-2") {
                // Not executed = �bne
                if ($this->dbquery->checkFilter("to_date")) {
                    $date = new Intraface_Date($this->dbquery->getFilter("to_date"));
                    if ($date->convert2db()) {
                        // Poster der er executed eller cancelled efter dato, og sikring at executed stadig er det, da faktura kan s�ttes tilbage.
                        $this->dbquery->setCondition("(date_executed >= \"".$date->get()."\" AND status = 2) OR (date_cancelled >= \"".$date->get()."\") OR status < 2");
                    }
                } else {
                    // Hvis der ikke er nogen dato s� tager vi alle dem som p� nuv�rende tidspunkt har status under
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
                    // tager dem som p� nuv�rende tidspunkt har den angivet status
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

    function isFilledIn()
    {
        $db = new DB_Sql;
        $db->query("SELECT id FROM invoice_reminder WHERE intranet_id = " . $this->kernel->intranet->get('id'));
        return $db->numRows();
    }

    function getMaxNumber()
    {
        $this->db = new DB_Sql;
        $this->db->query("SELECT MAX(number) AS max_number FROM invoice_reminder WHERE intranet_id = ".$this->kernel->intranet->get("id"));
        $this->db->nextRecord(); // Hvis der ikke er nogle poster er dette bare den f�rste
        $number = $this->db->f("max_number");
        return $number;
    }
}