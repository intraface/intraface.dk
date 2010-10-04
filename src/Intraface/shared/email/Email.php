<?php
/**
 * Queues and saves e-mails
 *
 * Emails must be connected to the module from which they are saved, so you are always
 * able to find an email.
 *
 * @package Intraface
 * @author  Lars Olesen <lars@legestue.net>
 * @version @package-version@
 *
 */
require_once 'Intraface/functions.php';
require_once 'Intraface/modules/contact/Contact.php';

class Email extends Intraface_Standard
{
    public $kernel;
    public $error;
    public $id;
    public $contact;
    public $type;
    protected $dbquery;
    protected $db;

    /**
     * Constructor
     *
     * @param object  $kernel Kernel object
     * @param integer $id     E-mail id
     *
     * @return void
     */
    function __construct($kernel, $id = 0)
    {
        $this->kernel = $kernel;

        $this->id = (int)$id;
        $this->error = new Intraface_Error;

        $this->type = array(
            1 => 'quotation',
            2 => 'order',
            3 => 'invoice',
            4 => 'creditnote',
            5 => 'reminder',
            6 => 'todo',
            7 => 'newslist',
            8 => 'newsletter',
            9 => 'contact',
            10 => 'electronic_invoice',
            11 => 'email_to_search',
            12 => 'webshop',
            13 => 'onlinepayment'
        );

        $this->status = array(
            1 => 'draft',
            2 => 'outbox',
            3 => 'sent'
        );

        if ($this->id > 0) {
            $this->load();
        }
    }

    function getKernel()
    {
        return $this->kernel;
    }

    /**
     * @return boolean
     */
    function load()
    {
        if ($this->id == 0) {
            return false;
        }

        $db = new DB_Sql;
        $db->query('SET NAMES utf8'); /* To be removed when everything is in utf-8 */
        $sql = "SELECT id, email.date_sent, DATE_FORMAT(date_sent, '%d-%m-%Y') as date_sent_dk, subject, from_name, from_email, user_id, body, status, contact_id, contact_person_id, type_id, belong_to_id, status, bcc_to_user FROM email WHERE intranet_id = ".$this->kernel->intranet->get('id')." AND id = " . $this->id;
        $db->query($sql);
        if (!$db->nextRecord()) {
            return false;
        }

        $this->value['id'] = $db->f('id');
        $this->value['subject'] = $db->f('subject');
        $this->value['from_name'] = $db->f('from_name');
        $this->value['from_email'] = $db->f('from_email');
        $this->value['body'] = $db->f('body');
        $this->value['status'] = $db->f('status');
        $this->value['contact_id'] = $db->f('contact_id');
        $this->value['contact_person_id'] = $db->f('contact_person_id');
        $this->value['type_id'] = $db->f('type_id');
        $this->value['belong_to_id'] = $db->f('belong_to_id');
        $this->value['status'] = $this->status[$db->f('status')];
        $this->value['status_key'] = $db->f('status');
        $this->value['user_id'] = $db->f('user_id');
        $this->value['bcc_to_user'] = $db->f('bcc_to_user');
        $this->value['date_sent_dk'] = $db->f('date_sent_dk');
        $this->value['date_sent'] = $db->f('date_sent');

        if ($db->f('contact_id') == 0) {
            return false;
        }

        return true;
    }

    /**
     * @return Contact object
     */
    function getContact()
    {
        $this->kernel->useModule('contact');
        $this->contact = new Contact($this->kernel, $this->get('contact_id'));

        if ($this->get('contact_person_id') != 0 && $this->contact->get('type') == 'corporation') {
            $this->contact->loadContactPerson($this->get('contact_person_id'));
        }

        $this->value['contact_email'] = $this->contact->address->get('email');
        $this->value['contact_name'] = $this->contact->address->get('name');
        return $this->contact;
    }

    /**
     * @param struct $var Values to validate
     *
     * @return boolean
     */
    function validate($var)
    {
        $validator = new Intraface_Validator($this->error);
        if ($this->id == 0) {
            $validator->isNumeric($var['belong_to'], 'belong_to');
            $validator->isNumeric($var['type_id'], 'type_id');
            $validator->isNumeric($var['contact_id'], 'contact_id');
        }

        $validator->isString($var['subject'], 'there was an error in subject', '');
        $validator->isString($var['body'], 'there was an error in body', '');
        settype($var['from_email'], 'string'); //
        $validator->isEmail($var['from_email'], 'there was an error in from email', 'allow_empty');
        settype($var['from_name'], 'string'); //
        $validator->isString($var['from_name'], 'there was and error in from name', '', 'allow_empty');

        if ($this->error->isError()) {
            return false;
        }

        return true;
    }

    /**
     * @param struct $var Values to save
     *
     * @return integer id of the saved email
     */
    function save($var)
    {
        $var = safeToDb($var);

        if (!$this->validate($var)) {
            return 0;
        }
        $db = new DB_Sql;
        $db->query('SET NAMES utf8');

        if ($this->id == 0) {
            $sql_type = "INSERT INTO ";
            $sql_end = ", date_created = NOW(),
                belong_to_id = ".(int)$var['belong_to'] . ",
                type_id = ".(int)$var['type_id'] . ",
                contact_id=".$var['contact_id'];
        } else {
            $sql_type = "UPDATE ";
            $sql_end = " WHERE id = " . $this->id;
        }

        if (!empty($var['date_deadline'])) {
            $date_deadline = "'".$var['date_deadline']."'";
        } else {
            $date_deadline = 'NOW()';
        }
        $sql_extra = '';
        // gemme userid hvis vi er inde i systemet
        if (is_object($this->kernel->user) AND $this->kernel->user->get('id') > 0) {
            //$db->query("UPDATE email SET user_id = ".$this->kernel->user->get('id')." WHERE id = " . $this->id);
            $sql_extra = ', user_id = ' . $db->quote($this->kernel->user->get('id'), 'integer');
        }

        if (!isset($var['contact_person_id'])) {
            $var['contact_person_id'] = 0;
        }

        if (!isset($var['bcc_to_user'])) {
            $var['bcc_to_user'] = 0;
        }

        // status 1 = draft
        $sql = $sql_type . " email SET
            contact_person_id = ".(int)$var['contact_person_id'].",
            bcc_to_user = ".(int)$var['bcc_to_user'].",
            date_updated = NOW(),
            intranet_id = " . $this->kernel->intranet->get('id') . ",
            subject = '".$var['subject']."',
            body = '".$var['body']."',
            date_deadline = ".$date_deadline.",
            status = 1 " . $sql_extra;

        if (isset($var['from_name'])) {
            $sql .= ", from_name = '".$var['from_name']."'";
        }
        if (isset($var['from_email'])) {
            $sql .= ", from_email = '".$var['from_email']."'";
        }

        $sql .= $sql_end;


        $db->query($sql);

        if ($this->id == 0) {
            $this->id = $db->insertedId();
        }

        if ($this->id > 0) {
            $this->load();
        }
        return $this->id;
    }

    /**
     * Saves error msg in the database
     *
     * @param string $error Error msg to save
     *
     * @return boolean
     */
    function saveErrorMsg($error)
    {
        $db = new DB_Sql;
        $db->query("UPDATE email SET error_msg = '".$error."' WHERE id = " . $this->id);
        return true;
    }

    /**
     * Sets date and status when sent
     *
     * @return boolean
     */
    function setIsSent()
    {
        if ($this->id == 0) {
            return false;
        }
        $db = new DB_Sql;
        $db->query("UPDATE email SET status = 3, date_sent = NOW() WHERE id = " . $this->id);
        return true;
    }

    /**
     * Checks if e-mail can be sent
     *
     * @return boolean
     */
    function isReadyToSend()
    {
        if ($this->id == 0) {
            $this->error->set('the message can not be send because it has no id');
            return false;
        }
        if ($this->get('from_email') == '' && (!isset($this->kernel->intranet->address) || $this->kernel->intranet->address->get('email') == '')) {
            $this->error->set('you need to fill in an e-mail address for the intranet, to be able to send mails');
            return false;
        }

        return true;
    }

    function queue()
    {
        if (!$this->isReadyToSend()) {
            return false;
        }

        $db = new DB_Sql;
        // Putter e-mailen i outboxen (status = 2)
        $db->query("UPDATE email SET status = 2 WHERE intranet_id = ".$this->kernel->intranet->get('id')." AND id = " . $this->id);
        return true;
    }

    function getTo()
    {
        $contact = $this->getContact();
        if ($this->get('contact_id') == 0 OR !is_object($contact)) {
            $this->error->set('Der kunne ikke sendes e-mail til email #' . $this->get('id') . ' fordi der ikke var nogen kunde sat');
            return false;
        }

        $validator = new Intraface_Validator($this->error);

        if ($contact->get('type') == 'corporation' && $this->get('contact_person_id') != 0) {
            $contact->loadContactPerson($this->get('contact_person_id'));
            if ($validator->isEmail($contact->contactperson->get('email'))) {
                return array($contact->contactperson->get('email') => $contact->contactperson->get('name'));
            }
        }

        if($validator->isEmail($contact->address->get('email'))) {
            return array($contact->address->get('email') => $contact->address->get('name'));
        }

        return false;
    }

    function getFrom()
    {
        if ($this->get('from_email')) {
            if ($this->get('from_name')) {
                return array($this->get('from_email') => $this->get('from_name'));
            } else {
                return array($this->get('from_email'));
            }
        } else { // Standardafsender
            return array($this->kernel->intranet->address->get('email') => $this->kernel->intranet->address->get('name'));
        }
    }

    function getBody()
    {
        return $this->get('body');
    }

    function getSubject()
    {
        return $this->get('subject');
    }

    /**
     * Der er ingen grund til at man kan �ndre en attachment
     * Man kan gemme og slette
     *
     * Der skal knyttes flere attachments til en e-mail
     *
     * @param integer $file_id  Id of file in the file system
     * @param string  $filename Which filename to use
     *
     * @return boolean
     */
    function attachFile($file_id, $filename)
    {
        if (!is_numeric($file_id)) {
            $this->error->set('Fil-id skal være et tal');
        }
        if (empty($filename)) {
            $this->error->set('Navnet skal være en streng');
        }

        if ($this->error->isError()) {
            return 0;
        }

        $db = new DB_Sql;
        $db->query("INSERT INTO email_attachment
            SET
                email_id = '".$this->get('id')."',
                intranet_id = ".$this->kernel->intranet->get('id').",
                filename = '".$filename."',
                file_id = '".$file_id."'");
        return 1;
    }

    /**
     * @return array with attachments
     */
    function getAttachments()
    {
        $db = new DB_Sql;
        $db->query("SELECT file_id, filename FROM email_attachment
            WHERE intranet_id = " .$this->kernel->intranet->get('id') . " AND email_id = " . $this->id);
        $file = array();
        $i = 0;
        while ($db->nextRecord()) {

            $file[$i]['id'] = $db->f('file_id');
            $file[$i]['filename'] = $db->f('filename');
            $i++;
        }
        return $file;
    }

    /**
     * @deprecated
     *
     * @todo Denne funktion bør nok erstatte det meste af funktionen send(), s� send()
     *       netop kun sender en e-mail!
     *
     * @return boolean
     */
    function sendAll($mailer)
    {
        $gateway = new Intraface_shared_email_EmailGateway($this->getKernel());
        return $gateway->sendAll($mailer);
    }

    function delete()
    {
        if ($this->get('status') == 'sent' OR $this->id == 0) {
            return false;
        }
        $db = new DB_Sql;
        $db->query("DELETE FROM email WHERE id = " . $this->id . " AND intranet_id = " . $this->kernel->intranet->get('id'));
        return true;
    }

    /**
     * @deprecated
     *
     * @return array
     */
    function getList()
    {
        $gateway = new Intraface_shared_email_EmailGateway($this->getKernel());
        return $gateway->getAll();
    }

    /**
     * @deprecated
     *
     * @return integer of how many are in queue ot be sent
     */
    function countQueue()
    {
        $gateway = new Intraface_shared_email_EmailGateway($this->getKernel());
        return $gateway->countQueue();
    }

    /**
     * Checks how many emails has been sent the last hour
     *
     * @deprecated
     *
     * @return integer with numbers of e-mails sent
     */
    function sentThisHour()
    {
        $gateway = new Intraface_shared_email_EmailGateway($this->kernel);
        return $gateway->sentThisHour();
    }
}