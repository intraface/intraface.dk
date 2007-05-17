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
require_once 'phpmailer/class.phpmailer.php';
require_once 'Intraface/Standard.php';
require_once 'Intraface/Error.php';
require_once 'Intraface/DBQuery.php';
require_once 'Intraface/functions/functions.php';
require_once 'Intraface/Validator.php';
require_once 'Intraface/3Party/Database/Db_sql.php';
require_once 'Intraface/modules/contact/Contact.php';

class Email extends Standard
{

    var $kernel;
    var $error;
    var $id;
    var $contact;
    var $dbquery;

    /**
     * Bruges til at sætte den øvre grænse for hvor mange e-mails der sendes i timen
     */
    var $allowed_limit = 180;

    /**
     * Bruges til at sætte en buffer i systemet, så den automatiske udsendelse af
     * emails der er bagefter ikke optager hele kapaciteten for udsendelse af e-mails.
     */

    var $system_buffer = 50;

    /**
     * Konstruktør
     *
     * @param object  $kernel Kernel object
     * @param integer $id     E-mail id
     */
    function __construct($kernel, $id=0)
    {
        if (!is_object($kernel)) {
            trigger_error('E-mail kræver kernel', E_USER_ERROR);
        }
        $this->kernel = $kernel;

        $this->id = (int)$id;
        $this->error = new Error;

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
            12 => 'webshop'
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
    function createDBQuery()
    {
        $this->dbquery = new DBQuery($this->kernel, "email", "email.intranet_id = ".$this->kernel->intranet->get("id"));
        $this->dbquery->useErrorObject($this->error);
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
        $sql = "SELECT id, subject, from_name, from_email, user_id, body, status, contact_id, type_id, belong_to_id, status FROM email WHERE intranet_id = ".$this->kernel->intranet->get('id')." AND id = " . $this->id;
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
        $this->value['type_id'] = $db->f('type_id');
        $this->value['belong_to_id'] = $db->f('belong_to_id');
        $this->value['status'] = $this->status[$db->f('status')];
        $this->value['status_key'] = $db->f('status');
        $this->value['user_id'] = $db->f('user_id');

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
        $validator = new Validator($this->error);
        if ($this->id == 0) {
            $validator->isNumeric($var['belong_to'], 'belong_to');
            $validator->isNumeric($var['type_id'], 'type_id');
            $validator->isNumeric($var['contact_id'], 'contact_id');
        }

        $validator->isString($var['subject'], 'subject', '');
        $validator->isString($var['body'], 'body', '');
        settype($var['from_email'], 'string'); //
        $validator->isEmail($var['from_email'], 'from_email', 'allow_empty');
        settype($var['from_name'], 'string'); //
        $validator->isString($var['from_name'], 'from_name', '', 'allow_empty');

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


        // status 1 = draft
        $sql = $sql_type . " email SET
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
        $db = new DB_Sql;
        $db->query("SELECT COUNT(*) AS antal FROM email WHERE DATE_SUB(NOW(), INTERVAL 1 HOUR) < date_sent");
        // print $db->numRows();
        if (!$db->nextRecord()) {
            return 0;
        }
        return $db->f('antal');
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
     * Derefter sendes alle e-mails fra outboxen, så længe der ikke er sendt
     * over den timelige grænse.
     *
     * @param string $what_to_do Can be either send og queue
     *
     * @return boolean
     */
    function send($what_to_do = 'send')
    {

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
        // Sørger for at tjekke om der er sendt for mange e-mails. Hvis der er
        // returneres blot, og så sendes e-mailen senere. Vi lader som om det gik godt.
        //

        $sent_this_hour = $this->sentThisHour();

        if ($sent_this_hour >= $this->allowed_limit) {
            $this->error->set('Der er i øjeblikket kø i e-mail-systemet. Vi sender så hurtigt som muligt.');
            return 1;
        }

        $phpmailer = new Phpmailer;
        // opsætning
        $phpmailer->Mailer   = 'mail'; // Alternative to IsSMTP()
        $phpmailer->WordWrap = 75;
        $phpmailer->setLanguage('en', 'phpmailer/language/');
        // $phpmailer->ConfirmReadingTo = $this->kernel->intranet->address->get('email');


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

        // Modtager
        $contact = $this->getContact();

        if ($this->get('contact_id') == 0 OR !is_object($contact)) {
            $this->error->set('Der kunne ikke sendes e-mail til email #' . $this->get('id') . ' fordi der ikke var nogen kunde sat');
        }

        $phpmailer->AddAddress($contact->address->get('email'),
                              $contact->address->get('name'));

        // E-mail
        $phpmailer->Subject = $this->get('subject');
        $phpmailer->Body    = $this->get('body');

        $attachments = $this->getAttachments();

        if (is_array($attachments) AND count($attachments) > 0) {
            $this->kernel->useShared('filehandler');
            foreach ($attachments AS $file) {

                $filehandler = new FileHandler($this->kernel, $file['id']);
                // lille hack med at sætte uploadpath på

                if (!$phpmailer->addAttachment($filehandler->upload_path . $filehandler->get('server_file_name'), $file['filename'])) {
                    $this->error->set('Kunne ikke vedhæfte filen til e-mailen');
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
     * Der er ingen grund til at man kan ændre en attachment
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
     * @todo Denne funktion bør nok erstatte det meste af funktionen send(), så send()
     *       netop kun sender en e-mail!
     *
     * @return boolean
     */
    function sendAll()
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

        while ($db->nextRecord()) {
            $email = new Email($this->kernel, $db->f('id'));
            $email->send();
        }
        return 1;

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
        $db = new DB_Sql;
        $this->dbquery->setSorting("email.date_created DESC");
        $db = $this->dbquery->getRecordset("email.id, email.subject, email.status, email.contact_id", "", false);
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

}
?>