<?php
class Intraface_shared_email_EmailGateway
{
    protected $kernel;
    protected $dbquery;

    /**
     * Sets upper limit for how many e-mails can be sent an hour
     *
     * @var integer
     */
    public $allowed_limit = 300;

    /**
     * A buffer to make sure that automatic sending does not take up all
     * the systems capacity fore sending e-mails.
     *
     * @var integer
     */
    public $system_buffer = 50;
    public $error;
    protected $status;

    /**
     * Constructor
     *
     * @param object $kernel
     *
     * @return void
     */
    function __construct($kernel)
    {
        $this->kernel = $kernel;
        $this->error = new Intraface_Error();

        $this->status = array(
            1 => 'draft',
            2 => 'outbox',
            3 => 'sent'
        );
    }

    /**
     * @return DBQuery object
     */
    function getDBQuery()
    {
        if ($this->dbquery) {
            return $this->dbquery;
        }
        $this->dbquery = new Intraface_DBQuery($this->kernel, "email", "email.intranet_id = ".$this->kernel->intranet->get("id"));
        $this->dbquery->useErrorObject($this->error);
        return $this->dbquery;
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

    function findById($id)
    {
        return new Email($this->kernel, $id);
    }

    /**
     * @return integer of how many are in queue ot be sent
     */
    function countQueue()
    {
        $db = new DB_Sql;
        $db->query("SELECT COUNT(*) AS antal FROM email WHERE status = 2 AND intranet_id = " . $this->kernel->intranet->get('id'));
        $this->value['outbox'] = 0;
        if ($db->nextRecord()) {
            $this->value['outbox'] = $db->f('antal');
        }
        return $this->value['outbox'];
    }

    function getAll()
    {
        $db = $this->getDBQuery()->getRecordset("email.id, email.subject, email.status, email.date_sent, DATE_FORMAT(date_sent, '%d-%m-%Y') as date_sent_dk, email.contact_id", "", false);
        $i = 0;
        $list = array();
        while ($db->nextRecord()) {
            $list[$i]['id'] = $db->f('id');
            $list[$i]['date_sent_dk'] = $db->f('date_sent_dk');
            $list[$i]['date_sent'] = $db->f('date_sent');
            $list[$i]['subject'] = $db->f('subject');
            $list[$i]['status'] = $this->status[$db->f('status')];

            if ($db->f('contact_id') == 0) {
                $this->error->set('Kan ikke finde #' . $db->f('id') . ' fordi den ikke har noget kontakt_id');
                continue;
            }
            $this->kernel->useModule('contact');
            $contact = new Contact($this->kernel, $db->f('contact_id'));
            if (!is_object($contact->address)) {
                continue;
            }
            $list[$i]['contact_name'] = $contact->address->get('name');
            $list[$i]['contact_id'] = $contact->get('id');
            $i++;
        }
        return $list;
    }

    /**
     * Checks how many emails has been sent the last hour
     *
     * @return integer with numbers of e-mails sent
     */
    function sentThisHour()
    {
        $db = new DB_Sql;
        $db->query("SELECT COUNT(*) AS antal FROM email WHERE DATE_SUB(NOW(), INTERVAL 1 HOUR) < date_sent");
        if (!$db->nextRecord()) {
            return 0;
        }
        return $db->f('antal');
    }

    /**
     * @todo Denne funktion b�r nok erstatte det meste af funktionen send(), s� send()
     *       netop kun sender en e-mail!
     *
     * @return boolean
     */
    function sendAll($mailer)
    {
        if (!is_object($mailer)) {
            throw new Exception('A valid mailer object is needed');
        }

        $sent_this_hour = $this->sentThisHour();

        $limit_query = abs($this->allowed_limit-$sent_this_hour-$this->system_buffer);

        $sql = "SELECT id
                FROM email
                WHERE status = 2
                    AND date_deadline <= NOW()
                    AND intranet_id = " . $this->kernel->intranet->get('id') . "
                    AND contact_id > 0
                LIMIT " . $limit_query;
        $db = new DB_Sql;
        $db->query($sql);

        while ($db->nextRecord()) {
            $email = new Email($this->kernel, $db->f('id'));
            // could be good, but stops sending the rest of the emails if one has an error.
            // $email->error = &$this->error;
            $email->send($mailer);
        }
        return true;
    }

    function getEmailsToSend()
    {
        $sent_this_hour = $this->sentThisHour();

        $limit_query = abs($this->allowed_limit-$sent_this_hour-$this->system_buffer);

        $sql = "SELECT id
                FROM email
                WHERE status = 2
                    AND date_deadline <= NOW()
                    AND intranet_id = " . $this->kernel->intranet->get('id') . "
                    AND contact_id > 0
                LIMIT " . $limit_query;
        $db = new DB_Sql;
        $db->query($sql);

        $emails = array();
        while ($db->nextRecord()) {
            $emails[] = new Email($this->kernel, $db->f('id'));

        }
        return $emails;
    }
}