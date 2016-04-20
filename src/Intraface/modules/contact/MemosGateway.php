<?php
class Intraface_modules_contact_MemosGateway
{
    protected $db;
    protected $dbquery;
    protected $kernel;
    protected $error;

    /**
     * @param object $kernel
     */
    function __construct($kernel)
    {
        $this->db = MDB2::singleton(DB_DSN);
        $this->kernel = $kernel;
        $this->error = new Intraface_Error;
    }

    function findById($id)
    {
        $db = MDB2::singleton(DB_DSN);
        $result = $db->query("SELECT contact_id FROM contact_reminder_single WHERE intranet_id = ".$db->quote($this->kernel->intranet->get('id'), 'integer')." AND id = ".$db->quote($id, 'integer')."");
        if (PEAR::isError($result)) {
            throw new Exception('result is an error in Contact_reminder_single->factory');
        }

        $row = $result->fetchRow();
        $contact = new Contact($this->kernel, $row['contact_id']);
        if ($contact->get('id') == 0) {
            throw new Exception("Invalid contact id in ContactReminder->factory");
        }

        return new ContactReminder($contact, $id);
    }

    public function getDBQuery()
    {
        if ($this->dbquery) {
            return $this->dbquery;
        }

        $this->dbquery = new Intraface_DBQuery(
            $this->kernel,
            "contact_reminder_single",
            "contact_reminder_single.active = 1
            	AND contact_reminder_single.intranet_id = " .
                    $this->db->quote(
                        $this->kernel->intranet->get("id"),
                        'integer'
                    )
        );
        $this->dbquery->setJoin("INNER", "contact", "contact_reminder_single.contact_id = contact.id", "contact.active = 1 AND contact.intranet_id = ".$this->db->quote($this->kernel->intranet->get("id"), 'integer'));
        $this->dbquery->useErrorObject($this->error);

        return $this->dbquery;
    }

    public function findByContactId($contact_id)
    {
        $this->getDBQuery()->setSorting('reminder_date');
        $this->getDBQuery()->setCondition('contact_id = '.$this->db->quote($contact_id, 'integer'));
        $this->getDBQuery()->setCondition('status_key = '.$this->db->quote(1, 'integer'));

        $db = $this->getDBQuery()->getRecordset("contact_reminder_single.id, DATE_FORMAT(contact_reminder_single.reminder_date, '%d-%m-%Y') AS dk_reminder_date, contact_reminder_single.reminder_date, contact_reminder_single.subject", "", false);
        $reminders = array();
        $i = 0;
        while ($db->nextRecord()) {
            //
            $reminders[$i]['id'] = $db->f("id");
            $reminders[$i]['reminder_date'] = $db->f("reminder_date");
            $reminders[$i]['dk_reminder_date'] = $db->f("dk_reminder_date");
            $reminders[$i]['subject'] = $db->f("subject");
            $reminders[$i]['contact_id'] = $contact_id;
            $i++;
        }
        return $reminders;
    }

    public function getAll()
    {
        $this->getDBQuery()->setSorting('reminder_date');
        $this->getDBQuery()->setCondition('status_key = '.$this->db->quote(1, 'integer'));

        $db = $this->getDBQuery()->getRecordset("contact_reminder_single.contact_id, contact_reminder_single.id, DATE_FORMAT(contact_reminder_single.reminder_date, '%d-%m-%Y') AS dk_reminder_date, contact_reminder_single.reminder_date, contact_reminder_single.subject", "", false);
        $reminders = array();
        $i = 0;
        while ($db->nextRecord()) {
            //
            $reminders[$i]['id'] = $db->f("id");
            $reminders[$i]['reminder_date'] = $db->f("reminder_date");
            $reminders[$i]['dk_reminder_date'] = $db->f("dk_reminder_date");
            $reminders[$i]['subject'] = $db->f("subject");
            $reminders[$i]['contact_id'] = $db->f("contact_id");
            $i++;
        }
        return $reminders;
    }
}
