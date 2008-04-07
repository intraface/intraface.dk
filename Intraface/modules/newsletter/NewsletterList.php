<?php
/**
 * List
 *
 * Skal håndtere de forskellige lister, man kan tilmelde sig på hjemmesiden.
 *
 * TODO    Burde nok finde et andet navn end liste.
 *
 * @package Intraface_Newsletter
 * @author  Lars Olesen <lars@legestue.net>
 * @version 1.0
 *
 */
require_once 'Intraface/Standard.php';
require_once 'DB/Sql.php';
require_once 'Intraface/Error.php';
require_once 'Intraface/Validator.php';
require_once 'Intraface/functions/functions.php';

class NewsletterList extends Standard
{
    public $value;
    private $id;
    public $kernel;
    public $error;

    /**
     * Constructor
     *
     * @param object  $kernel Kernel object
     * @param integer $id     Id fo the list
     *
     * @return void
     */
    public function __construct($kernel, $id = 0)
    {
        if (!is_object($kernel)) {
            trigger_error('Listadministration kræver Kernel', E_USER_ERROR);
        }
        $this->kernel = $kernel;

        $this->id = (int)$id;
        if ($this->id > 0) {
            $this->load();
        }
        $this->error = new Error;
    }

    /**
     * loads the list
     *
     * @return boolean
     */
    private function load()
    {
        $db  = new DB_Sql;
        $db2 = new DB_Sql;
        $db->query("SELECT * FROM newsletter_list WHERE active = 1 AND id = " . $this->id . " AND intranet_id = " . $this->kernel->intranet->get('id'));
        if (!$db->nextRecord()) {
            return false;
        }

        $this->value['id']                  = $db->f('id');
        $this->value['title']               = $db->f('title');
        $this->value['description']         = $db->f('description');
        $this->value['subscribe_subject']   = $db->f('subscribe_subject');
        $this->value['subscribe_message']   = $db->f('subscribe_message');
        $this->value['unsubscribe_message'] = $db->f('unsubscribe_message');
        $this->value['privacy_policy']      = $db->f('privacy_policy');
        $this->value['sender_name']         = $db->f('sender_name');
        $this->value['optin_link']          = $db->f('optin_link');
        if (empty($this->value['sender_name'])) {
            $this->value['sender_name'] = $this->kernel->intranet->get('name');
        }
        $this->value['reply_email'] = $db->f('reply_email');
        if (empty($this->value['reply_email'])) {
            $this->value['reply_email'] = $this->kernel->intranet->address->get('email');
        }
        $db2->query("SELECT *
            FROM newsletter_subscriber
            INNER JOIN contact ON newsletter_subscriber.contact_id = contact.id
            WHERE list_id = " . $db->f('id') . "
                AND newsletter_subscriber.intranet_id = " . $this->kernel->intranet->get('id') . "
                AND optin = 1
                AND contact.active = 1
                AND newsletter_subscriber.active = 1");
        $this->value['subscribers'] = $db2->numRows();

        return true;
    }

    /**
     * Gets the intranet
     *
     * @return object
     */
    public function getIntranet()
    {
        return $this->kernel->intranet;
    }

    /**
     * Validates
     *
     * @param array $var Array to validate
     *
     * @return boolean
     */
    private function validate($var)
    {
        $validator = new Validator($this->error);
        $validator->isString($var['title'], 'Titel er ikke ufdyldt korrekt');
        $validator->isString($var['sender_name'], 'Navn på afsender er ikke ufdyldt korrekt', '', 'allow_empty');
        $validator->isEmail($var['reply_email'], 'E-mail er ikke en gyldig e-mail', 'allow_empty');
        $validator->isString($var['description'], 'Beskrivelse er ikke gyldig', '<strong><em>', 'allow_empty');
        $validator->isString($var['subscribe_subject'], 'Subject til bekræftelse på tilmelding er ikke udfyldt korrekt', '', 'allow_empty');
        $validator->isString($var['subscribe_message'], 'Bekræftelse på tilmelding er ikke udfyldt korrekt', '', 'allow_empty');

        if ($this->error->isError()) {
            return false;
        }
        return true;
    }

    /**
     * Saves
     *
     * @param array $var Array to validate
     *
     * @return integer
     */
    function save($var)
    {
        $var = safeToDb($var);

        if (!$this->validate($var)) {
            return 0;
        }

        $db = new DB_Sql;
        // privacy_policy = \"".$var['privacy_policy']."\",
        // unsubscribe_message = \"".$var['unsubscribe_message']."\",

        $sql = "sender_name = \"".$var['sender_name']."\",
            reply_email = \"".$var['reply_email']."\",
            description = \"".$var['description']."\",
            title = \"".$var['title']."\",
            optin_link = \"".$var['optin_link']."\",
            subscribe_subject = \"".$var['subscribe_subject']."\",
            subscribe_message = \"".$var['subscribe_message']."\"";
            // subscribe_option_key = ".$var['subscribe_option_key'].",

        if ($this->id > 0) {
            $db->query("UPDATE newsletter_list SET ".$sql.", date_changed = NOW() WHERE id = " . $this->id);
        } else {
            $db->query("INSERT INTO newsletter_list SET ".$sql.", intranet_id = " . $this->kernel->intranet->get('id').", date_created = NOW(), date_changed = NOW()");
            $this->id = $db->insertedId();
        }

        return $this->id;
    }

    /**
     * Deletes
     *
     * TODO Bør kun kunne slette lister hvor der ikke er breve der ikke er sendt
     *
     * @return boolean
     */
    function delete()
    {
        $db = new DB_Sql;
        $db->query("UPDATE newsletter_list SET active = 0 WHERE id = " . $this->id . " AND intranet_id = " . $this->kernel->intranet->get('id'));
        return true;
    }

    /**
     * Checks whether the list acutally exists
     *
     * @return boolean
     */
    function doesListExist()
    {
        $db = new DB_Sql;
        $db->query("SELECT id FROM newsletter_list WHERE id = " . $this->id . " AND intranet_id = " . $this->kernel->intranet->get('id'));
        return ($db->numRows() > 0);
    }

    /**
     * Gets a list with newsletters
     *
     * @return boolean
     */
    function getList()
    {
        $lists = array();
        $db    = new DB_Sql;
        $db2   = new DB_Sql;
        $i     = 0;
        $db->query("SELECT * FROM newsletter_list WHERE intranet_id = " . $this->kernel->intranet->get('id')." AND active = 1");
        while ($db->nextRecord()) {
            $list                     = new Newsletterlist($this->kernel, $db->f('id'));
            $lists[$i]['id']          = $db->f('id');
            $lists[$i]['title']       = $db->f('title');
            $lists[$i]['subscribers'] = $list->get('subscribers');
            $i++;
        }
        return $lists;
    }

}