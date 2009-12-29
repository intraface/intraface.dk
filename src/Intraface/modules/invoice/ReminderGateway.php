<?php
class Intraface_modules_invoice_ReminderGateway
{
    protected $kernel;

    function __construct($kernel)
    {
        $this->kernel = $kernel;
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
}