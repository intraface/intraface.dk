<?php
class Intraface_shared_email_EmailGateway
{
    protected $kernel;

    function __construct($kernel)
    {
        $this->kernel = $kernel;
    }

    function findCountByContactId($contact_id)
    {
        $db = new DB_Sql;
        $db->query('SELECT * FROM email WHERE contact_id = ' . $contact_id);
        return $db->numRows();
    }

    function setNewContactId($old_contact_id, $new_contact_id)
    {
        $db = new DB_Sql;
        $db->query('UPDATE email SET contact_id = ' . $new_contact_id . ' WHERE contact_id = ' . $old_contact_id);
        return true;
    }

    function getList()
    {
        $db = new DB_Sql;
        $this->getDBQuery()->setSorting("email.date_created DESC");
        $db = $this->getDBQuery()->getRecordset("email.id, email.subject, email.status, email.contact_id", "", false);
        $i = 0;
        $list = array();
        while ($db->nextRecord()) {
            $list[$i]['id'] = $db->f('id');
            $list[$i]['subject'] = $db->f('subject');
            $list[$i]['status'] = $this->status[$db->f('status')];

            if ($db->f('contact_id') == 0) {
                $this->error->set('Kan ikke finde #' . $db->f('id') . ' fordi den ikke har noget kontakt_id');
                continue;
            }
            $this->kernel->useModule('contact');
            $contact = new Contact($this->kernel, $db->f('contact_id'));
            if (!is_object($contact->address)) continue;
            $list[$i]['contact_name'] = $contact->address->get('name');
            $list[$i]['contact_id'] = $contact->get('id');
            $i++;
        }
        return $list;
    }
}