<?php
/**
 * Queues and saves e-mails
 *
 * Must have an upper limit for how many emails are sent an hour, as Dreamhost only
 * accepts sending 200 mails.
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
    protected $dbquery;

    /**
     * Bruges til at s�tte den �vre gr�nse for hvor mange e-mails der sendes i timen
     */
    //public $allowed_limit = 180;

    /**
     * Bruges til at s�tte en buffer i systemet, s� den automatiske udsendelse af
     * emails der er bagefter ikke optager hele kapaciteten for udsendelse af e-mails.
     */

    //public $system_buffer = 50;

    /**
     * Konstrukt�r
     *
     * @param object  $kernel Kernel object
     * @param integer $id     E-mail id
     */
    function __construct($kernel, $id=0)
    {
        if (!is_object($kernel)) {
            trigger_error('E-mail kr�ver kernel', E_USER_ERROR);
        }
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
            11 => 'email_to_search',  // kan ikke helt huske hvad den her er?
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

    /**
     * @return DBQuery object
     */
    function getDBQuery()
    {
        if ($this->dbquery) return $this->dbquery;
        $this->dbquery = new Intraface_DBQuery($this->kernel, "email", "email.intranet_id = ".$this->kernel->intranet->get("id"));
        $this->dbquery->useErrorObject($this->error);
        return $this->dbquery;
    }

    /**
     * @return boolean
     */
    function load()
    {
        if ($this->id == 0) {
            return 0;
        }

        $db = new DB_Sql;
        $sql = "SELECT id, subject, from_name, from_email, user_id, body, status, contact_id, contact_person_id, type_id, belong_to_id, status, bcc_to_user FROM email WHERE intranet_id = ".$this->kernel->intranet->get('id')." AND id = " . $this->id;
        $db->query($sql);
        if (!$db->nextRecord()) {
            return 0;
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

        if ($db->f('contact_id') == 0) {
            return 0;
        }

        return 1;
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
            return 0;
        }

        return 1;
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
     * Checks how many emails has been sent the last hour
     *
     * @return integer with numbers of e-mails sent
     */
    function sentThisHour()
    {
        $gateway = new Intraface_shared_email_EmailGateway($this->kernel);
        return $gateway->sentThisHour();
        /*
        $db = new DB_Sql;
        $db->query("SELECT COUNT(*) AS antal FROM email WHERE DATE_SUB(NOW(), INTERVAL 1 HOUR) < date_sent");
        if (!$db->nextRecord()) {
            return 0;
        }
        return $db->f('antal');
        */
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
            return 0;
        }
        if ($this->get('from_email') == '' && (!isset($this->kernel->intranet->address) || $this->kernel->intranet->address->get('email') == '')) {
            $this->error->set('you need to fill in an e-mail address for the intranet, to be able to send mails');
            return 0;
        }

        return 1;
    }

    /**
     * Hvis der er en aktuel e-mail puttes den i outbox'en.
     * Derefter sendes alle e-mails fra outboxen, s� l�nge der ikke er sendt
     * over den timelige gr�nse.
     *
     * @param string $what_to_do Can be either send og queue
     *
     * @return boolean
     */
    function send($phpmailer, $what_to_do = 'send')
    {
        if (!is_object($phpmailer)) {
            throw new Exception('A valid mailer is not provided to the send method');
        }

        if (!$this->isReadyToSend()) {
            return false;
        }

        $db = new DB_Sql;

        //
        // Putter e-mailen i outboxen
        // status 2 er outbox
        //
        $db->query("UPDATE email SET status = 2 WHERE intranet_id = ".$this->kernel->intranet->get('id')." AND id = " . $this->id);

        if ($what_to_do == 'queue') {
            return 1;
        }

        //
        // S�rger for at tjekke om der er sendt for mange e-mails. Hvis der er
        // returneres blot, og s� sendes e-mailen senere. Vi lader som om det gik godt.
        //

        $gateway = new Intraface_shared_email_EmailGateway($this->kernel);

        $sent_this_hour = $gateway->sentThisHour();

        if ($sent_this_hour >= $gateway->allowed_limit) {
            $this->error->set('Der er i øjeblikket kø i e-mail-systemet. Vi sender så hurtigt som muligt.');
            return 1;
        }

        // Make sure it is cleared from earlier use.
        $phpmailer->ClearReplyTos();
        $phpmailer->ClearAllRecipients();
        $phpmailer->ClearAttachments();

        // Sender
        if ($this->get('from_email')) {
            $phpmailer->From = $this->get('from_email');
            if ($this->get('from_name')) {
                $phpmailer->FromName = $this->get('from_name');
            } else {
                $phpmailer->FromName = $this->get('from_email');
            }
        } else { // Standardafsender
            $phpmailer->From = $this->kernel->intranet->address->get('email');
            $phpmailer->FromName = $this->kernel->intranet->address->get('name');
        }

        $phpmailer->Sender = $phpmailer->From;
        $phpmailer->AddReplyTo($phpmailer->From);

        // Reciever
        $contact = $this->getContact();
        if ($this->get('contact_id') == 0 OR !is_object($contact)) {
            $this->error->set('Der kunne ikke sendes e-mail til email #' . $this->get('id') . ' fordi der ikke var nogen kunde sat');
        }

        if ($contact->get('type') == 'corporation' && $this->get('contact_person_id') != 0) {
            $contact->loadContactPerson($this->get('contact_person_id'));
            $validator = new Intraface_Validator($this->error);
            if ($validator->isEmail($contact->contactperson->get('email'))) {
                $phpmailer->AddAddress($contact->contactperson->get('email'), $contact->contactperson->get('name'));
            } else {
                $phpmailer->AddAddress($contact->address->get('email'), $contact->address->get('name'));
            }
        } else {
            $phpmailer->AddAddress($contact->address->get('email'), $contact->address->get('name'));
        }

        if ($this->get('bcc_to_user')) {
            $phpmailer->addBCC($this->kernel->user->getAddress()->get('email'), $this->kernel->user->getAddress()->get('name'));
        }

        // E-mail
        $phpmailer->Subject = $this->get('subject');
        $phpmailer->Body    = $this->get('body');

        $attachments = $this->getAttachments();

        if (is_array($attachments) AND count($attachments) > 0) {
            $this->kernel->useShared('filehandler');
            foreach ($attachments AS $file) {

                $filehandler = new FileHandler($this->kernel, $file['id']);
                // lille hack med at s�tte uploadpath p�

                if (!$phpmailer->addAttachment($filehandler->getUploadPath() . $filehandler->get('server_file_name'), $file['filename'])) {
                    $this->error->set('Kunne ikke vedh�fte filen til e-mailen');
                }
            }
        }

        if ($this->error->isError()) {
            return 0;
        }

        // Sender e-mailen
        if (!$phpmailer->Send()) {
            $this->error->set('Der blev ikke sendt en e-mail til ' . $this->contact->address->get('email'));
            $this->saveErrorMsg($phpmailer->ErrorInfo);
        } else {
            $this->setIsSent();
            $this->saveErrorMsg('success');
        }

        $phpmailer->clearAddresses();

        return 1;
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
            $this->error->set('Fil-id skal v�re et tal');
        }
        if (empty($filename)) {
            $this->error->set('Navnet skal v�re en streng');
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
     * @todo Denne funktion b�r nok erstatte det meste af funktionen send(), s� send()
     *       netop kun sender en e-mail!
     *
     * @return boolean
     */
    function sendAll($mailer)
    {
        $gateway = new Intraface_shared_email_EmailGateway($this->getKernel());
        return $gateway->sendAll($mailer);
        /*
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
        return 1;
		*/
    }

    function delete()
    {
        if ($this->get('status') == 'sent' OR $this->id == 0) {
            return 0;
        }
        $db = new DB_Sql;
        $db->query("DELETE FROM email WHERE id = " . $this->id . " AND intranet_id = " . $this->kernel->intranet->get('id'));
        return 1;
    }


    function getList()
    {
        $gateway = new Intraface_shared_email_EmailGateway($this->getKernel());
        return $gateway->getAll();

        /*
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
    	*/
    }

    /**
     * @return integer of how many are in queue ot be sent
     */
    function countQueue()
    {
        $gateway = new Intraface_shared_email_EmailGateway($this->getKernel());
        return $gateway->countQueue();

        /*
        $db = new DB_Sql;
        $db->query("SELECT COUNT(*) AS antal FROM email WHERE status = 2 AND intranet_id = " . $this->kernel->intranet->get('id'));
        $this->value['outbox'] = 0;
        if ($db->nextRecord()) {
            $this->value['outbox'] = $db->f('antal');
        }
        return $this->value['outbox'];
        */
    }
}