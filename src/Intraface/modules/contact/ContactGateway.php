<?php
class Intraface_modules_contact_ContactGateway
{
    protected $kernel;
    protected $db;
    protected $dbquery;
    protected $error;

    function __construct($kernel, DB_Sql $db)
    {
        $this->kernel = $kernel;
        $this->db = $db;
        $this->error = new Intraface_Error;
    }

    public function getDBQuery()
    {
        if ($this->dbquery) {
            return $this->dbquery;
        }
        $this->dbquery = new Intraface_DBQuery($this->kernel, "contact", "contact.active = 1 AND contact.intranet_id = ".$this->kernel->intranet->get("id"));
        $this->dbquery->setJoin("LEFT", "address", "address.belong_to_id = contact.id", "address.active = 1 AND address.type = 3");
        $this->dbquery->useErrorObject($this->error);

        return $this->dbquery;
    }

    function findById($id)
    {
        return new Contact($this->kernel, $id);
    }

    function findByEmail($value)
    {
        $this->db->query("SELECT address.belong_to_id AS id FROM contact INNER JOIN address ON address.belong_to_id = contact.id WHERE address.email = '".$value."' AND contact.intranet_id = " . $this->kernel->intranet->get('id') . " AND address.active = 1 AND contact.active = 1");
        if (!$this->db->nextRecord()) {
            throw new Exception('Contact not found');
        }
        return $this->findById($this->db->f('id'));
    }

    function findByCode($value)
    {
        $this->db->query("SELECT id FROM contact WHERE code  = '".$value."' AND contact.intranet_id = " . $this->kernel->intranet->get('id'));
        if (!$this->db->nextRecord()) {
            throw new Exception('Contact not found');
        }
        return $this->findById($this->db->f('id'));
    }

    function findByUsername($value)
    {
        $this->db->query("SELECT id FROM contact WHERE username  = '".$value['username']."' AND password  = '".$value['password']."' AND contact.intranet_id = " . $kernel->intranet->get('id'));
        if (!$this->db->nextRecord()) {
            throw new Exception('Contact not found');
        }
        return $this->findById($this->db->f('id'));
    }

    function findByOpenId($value)
    {
        $this->db->query("SELECT id FROM contact WHERE openid_url  = '".$value."' AND contact.intranet_id = " . $kernel->intranet->get('id'));
        if (!$this->db->nextRecord()) {
            throw new Exception('Contact not found');
        }
        return $this->findById($this->db->f('id'));
    }


    /**
     * Public: Finde data til en liste
     *
     * @param string $parameter hvad er det?
     *
     * @return array indeholdende kundedata til liste
     */
    public function getList($parameter = "")
    {
        if ($this->getDBQuery()->checkFilter("search")) {
            $search = $this->getDBQuery()->getFilter("search");
            $this->getDBQuery()->setCondition("
                contact.number = '".$search."' OR
                address.name LIKE '%".$search."%' OR
                address.address LIKE '%".$search."%' OR
                address.email LIKE '%".$search."%' OR
                address.phone = '".$search."'");
        }

        $this->getDBQuery()->setSorting("address.name");

        $i = 0; // til at give arrayet en key

        $db = $this->getDBQuery()->getRecordset("contact.id, contact.number, contact.paymentcondition, address.name, address.email, address.phone, address.address, address.postcode, address.city", "", false);

        $contacts = array();
        while ($db->nextRecord()) {
            //
            $contacts[$i]['id'] = $db->f("id");
            $contacts[$i]['number'] = $db->f("number");
            $contacts[$i]['paymentcondition'] = $db->f("paymentcondition");
            $contacts[$i]['name'] = $db->f("name");
            $contacts[$i]['address'] = $db->f("address");
            $contacts[$i]['postcode'] = $db->f("postcode");
            $contacts[$i]['city'] = $db->f("city");
            $contacts[$i]['phone'] = $db->f("phone");
            $contacts[$i]['email'] = $db->f("email");

            if ($parameter == "use_address") {
                $address = Intraface_Address::factory("contact", $db->f("id"));
                $contacts[$i]['address'] = $address->get();
            }

            $i++;
        }
        return $contacts;
    }

    /**
     * Hente det maksimale kundenummer
     *
     * @return integer
     */
    public function getMaxNumber()
    {
        $db = new DB_Sql();
        $db->query("SELECT number FROM contact WHERE intranet_id = " . $this->kernel->intranet->get("id") . " ORDER BY number DESC");
        if (!$db->nextRecord()) {
            return 0;
        }
        return $db->f("number");
    }
}