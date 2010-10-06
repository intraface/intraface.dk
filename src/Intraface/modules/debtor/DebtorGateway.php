<?php
class Intraface_modules_debtor_DebtorGateway
{
    protected $kernel;
    protected $dbquery;
    public $error;
    protected $type;

    function __construct($kernel)
    {
        $this->kernel = $kernel;
        $this->error = new Intraface_Error;
    }

    function getType()
    {
        return $this->type;
    }

    /**
     * returns the possible debtor types!
     *
     * @return array types
     */
    static function getDebtorTypes()
    {
        return array(
            1 => 'quotation',
            2 => 'order',
            3 => 'invoice',
            4 => 'credit_note');
    }

    function setType($type)
    {
        $this->type = $type;
    }

    function getTypeKey()
    {
        return array_search( $this->type, $this->getDebtorTypes());
    }

    function getDBQuery()
    {
        if ($this->dbquery) {
            return $this->dbquery;
        }

        $this->dbquery = new Intraface_DBQuery($this->kernel, "debtor", "debtor.active = 1 AND debtor.intranet_id = ".$this->kernel->intranet->get("id"));
        $this->dbquery->setJoin("LEFT", "contact", "debtor.contact_id = contact.id AND contact.intranet_id = ".$this->kernel->intranet->get("id"), '');
        $this->dbquery->setJoin("LEFT", "address", "address.belong_to_id = contact.id AND address.active = 1 AND address.type = 3", '');
        $this->dbquery->setJoin("LEFT", "debtor_item", "debtor_item.debtor_id = debtor.id AND debtor_item.active = 1 AND debtor_item.intranet_id = ".$this->kernel->intranet->get("id"), '');

        $this->dbquery->useErrorObject($this->error);

        return $this->dbquery;
    }

    /**
     * Bruges til at lave en menu p� kontakten eller produktet
     *
     * @param string  $type    contact eller product
     * @param integer $type_id id p� contact eller product.
     *
     * @return integer
     */
    public function findCountByContactId($contact_id)
    {
                $sql = "SELECT id
                FROM debtor
                    WHERE intranet_id = " . $this->kernel->intranet->get("id") . "
                        AND contact_id = ".(int)$contact_id."
              AND type='".$this->type_key."'
              AND active = 1";

        $db = new DB_Sql;
        $db->query($sql);
        return $db->numRows();
    }

    function setNewContactId($old_contact_id, $new_contact_id)
    {
        $db = new DB_Sql;
        $db->query('UPDATE debtor SET contact_id = ' . $new_contact_id . ' WHERE contact_id = ' . $old_contact_id);
        return true;
    }

    function anyNew()
    {
        $db = new DB_Sql;
        $db->query('SELECT * FROM debtor WHERE date_created >=
        	DATE_SUB(NOW(),INTERVAL 1 DAY)
        	AND type = ' .$this->type_key . ' AND intranet_id = ' .$this->kernel->intranet->get('id'));
        return $db->numRows();
    }

    function findById($id)
    {
        if (is_int($id) && $id != 0) {
            $types = self::getDebtorTypes();

            $db = new DB_Sql;
            $db->query("SELECT type FROM debtor WHERE intranet_id = ".$this->kernel->intranet->get('id')." AND id = ".$id);
            if ($db->nextRecord()) {
                $type = $types[$db->f("type")];
            } else {
                throw new Exception("Invalid id for debtor in Debtor::factory");
            }
        } elseif (is_string($id) && $id != '') {
            $types = self::getDebtorTypes();

            $db = new DB_Sql;
            $db->query("SELECT type, id FROM debtor WHERE intranet_id = ".$this->kernel->intranet->get('id')." AND identifier_key = \"".$id."\"");
            if ($db->nextRecord()) {
                $type = $types[$db->f("type")];
                $id = $db->f("id");
            } else {
                throw new Exception("Invalid identifier_key for debtor in Debtor::factory");
            }
        }

        switch ($type) {
            case "quotation":
                $this->kernel->useModule("quotation");
                $object = new Quotation($this->kernel, intval($id));
                return $object;
                break;

            case "order":
                $this->kernel->useModule("order");
                $object = new Order($this->kernel, intval($id));
                break;

            case "invoice":
                $this->kernel->useModule("invoice");
                $object = new Invoice($this->kernel, intval($id));
                break;

            case "credit_note":
                $this->kernel->useModule("invoice");
                $object = new CreditNote($this->kernel, intval($id));
                break;

            default:
                throw new Exception("Ugyldig type: '".$type."'");
                break;
        }

        return $object;
    }

    function findByIdentifier($identifier)
    {
        if (is_int($id) && $id != 0) {
            $types = self::getDebtorTypes();

            $db = new DB_Sql;
            $db->query("SELECT type FROM debtor WHERE intranet_id = ".$kernel->intranet->get('id')." AND id = ".$id);
            if ($db->nextRecord()) {
                $type = $types[$db->f("type")];
            } else {
                throw new Exception("Invalid id for debtor in Debtor::factory");
            }
        } elseif (is_string($id) && $id != '') {
            $types = self::getDebtorTypes();

            $db = new DB_Sql;
            $db->query("SELECT type, id FROM debtor WHERE intranet_id = ".$kernel->intranet->get('id')." AND identifier_key = \"".$id."\"");
            if ($db->nextRecord()) {
                $type = $types[$db->f("type")];
                $id = $db->f("id");
            } else {
                throw new Exception("Invalid identifier_key for debtor in Debtor::factory");
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
                throw new Exception("Ugyldig type: '".$type."'");
                break;
        }

        return $object;
    }

    /**
     * Gets a list with debtors
     *
     * @return array
     */
    public function findAll()
    {
        $db = new DB_Sql;

        $this->dbquery = $this->getDBQuery();

        $this->dbquery->setCondition("debtor.type = ".$this->getTypeKey());

        if ($this->dbquery->checkFilter("contact_id")) {
            $this->dbquery->setCondition("debtor.contact_id = ".intval($this->dbquery->getFilter("contact_id")));
        }

        if ($this->dbquery->checkFilter("text")) {
            $this->dbquery->setCondition("(debtor.description LIKE \"%".$this->dbquery->getFilter("text")."%\" OR debtor.girocode = \"".$this->dbquery->getFilter("text")."\" OR debtor.number = \"".$this->dbquery->getFilter("text")."\" OR address.name LIKE \"%".$this->dbquery->getFilter("text")."%\")");
        }

        if ($this->dbquery->checkFilter("product_id")) {
            $this->dbquery->setCondition("debtor_item.product_id = ".$this->dbquery->getFilter('product_id'));
            if ($this->dbquery->checkFilter("product_variation_id")) {
                $this->dbquery->setCondition("debtor_item.product_variation_id = ".$this->dbquery->getFilter('product_variation_id'));
            } else {
                $this->dbquery->setCondition("debtor_item.product_variation_id = 0");
            }
        }

        if ($this->dbquery->checkFilter("date_field")) {
            if (in_array($this->dbquery->getFilter("date_field"), array('this_date', 'date_created', 'date_sent', 'date_executed', 'data_cancelled'))) {
                $date_field = $this->dbquery->getFilter("date_field");
            } else {
                $this->error->set("Ugyldigt datointerval felt");
            }
        } else {
            $date_field = 'this_date';
        }

        if ($this->dbquery->checkFilter("from_date")) {
            $date = new Intraface_Date($this->dbquery->getFilter("from_date"));
            if ($date->convert2db()) {
                $this->dbquery->setCondition("debtor.".$date_field." >= \"".$date->get()."\"");
            } else {
                $this->error->set("Fra dato er ikke gyldig");
            }
        }

        // Poster med fakturadato f�r slutdato.
        if ($this->dbquery->checkFilter("to_date")) {
            $date = new Intraface_Date($this->dbquery->getFilter("to_date"));
            if ($date->convert2db()) {
                $this->dbquery->setCondition("debtor.".$date_field." <= \"".$date->get()."\"");
            } else {
                $this->error->set("Til dato er ikke gyldig");
            }
        }
        // alle ikke bogf�rte skal findes
        if ($this->dbquery->checkFilter("not_stated")) {
            $this->dbquery->setCondition("voucher_id = 0");
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
                        $this->dbquery->setCondition("(debtor.date_executed >= \"".$date->get()."\" AND debtor.status = 2) OR (debtor.date_cancelled >= \"".$date->get()."\") OR debtor.status < 2");
                    }
                } else {
                    // Hvis der ikke er nogen dato s� tager vi alle dem som p� nuv�rende tidspunkt har status under
                    $this->dbquery->setCondition("debtor.status < 2");
                }

            } elseif ($this->dbquery->getFilter("status") == "-3") {
                //  Afskrevne. Vi tager f�rst alle sendte og executed.

                if ($this->get("type") != "invoice") {
                    throw new Exception("Afskrevne kan kun benyttes ved faktura");
                }

                $this->dbquery->setJoin("INNER", "invoice_payment", "invoice_payment.payment_for_id = debtor.id", "invoice_payment.intranet_id = ".$this->kernel->intranet->get("id")." AND invoice_payment.payment_for = 1");
                $this->dbquery->setCondition("invoice_payment.type = -1");

                if ($this->dbquery->checkFilter("to_date")) {
                    $date = new Intraface_Date($this->dbquery->getFilter("to_date"));
                    if ($date->convert2db()) {
                        // alle som er sendte p� datoen og som ikke er cancelled
                        $this->dbquery->setCondition("debtor.date_sent <= '".$date->get()."' AND debtor.status != 3");
                        $this->dbquery->setCondition("invoice_payment.payment_date <= '".$date->get()."'");
                    }
                } else {
                    // Hvis der ikke er nogen dato s� tager vi alle dem som p� nuv�rende tidspunkt har status under
                    $this->dbquery->setCondition("status = 1 OR status = 2");
                }
            } else {

                $this->dbquery->setCondition("debtor.status = ".intval($this->dbquery->getFilter("status")));

                /*
                // New date_field handles this instead
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
                        // This gives a problem: We have an invoice created 20/4 and is executed 5/5
                        // If we make a search: executed 1/4-30/4 the above invoice will not be calculated in with date search below.
                        // And if we make a search : executed 1/5-30/5 it will not even be included in that search.
                        // Why was this made in that way?
                        // $this->dbquery->setCondition("debtor.".$to_date_field." <= \"".$date->get()."\"");
                        // So instead we add this normal status search: Changed 12/7 2009 /Sune
                        $this->dbquery->setCondition("debtor.status = ".intval($this->dbquery->getFilter("status")));
                    }
                } else {
                    // tager dem som p� nuv�rende tidspunkt har den angivet status
                    $this->dbquery->setCondition("debtor.status = ".intval($this->dbquery->getFilter("status")));
                }
                */
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

        while ($db->nextRecord()) {

            $debtor = $this->findById((int)$db->f("id"));
            $list[$i] = $debtor->get();

            // $contact = new Contact($this->kernel, $db->f('contact_id'));
            if (is_object($debtor->contact->address)) {
                $list[$i]['contact'] = $debtor->contact->get();
                $list[$i]['contact']['address'] = $debtor->contact->address->get();

                // f�lgende skal v�k
                $list[$i]['contact_id'] = $debtor->contact->get('id');
                $list[$i]['name'] = $debtor->contact->address->get('name');
                $list[$i]['address'] = $debtor->contact->address->get('address');
                $list[$i]['postalcode'] = $debtor->contact->address->get('postcode');
                $list[$i]['city'] = $debtor->contact->address->get('city');

            }
            $debtor->destruct();
            unset($debtor);
            $i++;

        }
        unset($db);
        return $list;
    }

    /**
     * Funktion til at finde ud af, om der er oprettet nogen poster af den aktuelle bruger
     *
     * @return integer
     */
    public function isFilledIn()
    {
        $db = new DB_Sql;
        $db->query("SELECT id FROM debtor WHERE type = " . $this->getTypeKey() . " AND intranet_id = " . $this->kernel->intranet->get('id'));
        return $db->numRows();
    }
}