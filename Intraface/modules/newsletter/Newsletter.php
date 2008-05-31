<?php
/**
 * Handles the actual newsletter
 *
 * @package Intraface_Newsletter
 * @author  Lars Olesen <lars@legestue.net>
 * @version @package-version@
 * @see     NewsletterList
 * @see     NewsletterSubscriber
 */
require_once 'Intraface/Standard.php';
require_once 'Intraface/Error.php';
require_once 'Intraface/Validator.php';
require_once 'NewsletterSubscriber.php';

class Newsletter extends Intraface_Standard
{
    public $list; //object
    public $value = array();
    private $id;
    public $error;
    private $intranet_id;
    private $status = array(
        0 => 'created',
        1 => 'sent'
    );

    /**
     * Constructor
     *
     * @param object  $list Newsletter list object
     * @param integer $id   Newsletter id
     *
     * @return void
     */
    public function __construct($list, $id = 0)
    {
        if (!is_object($list)) {
            trigger_error('newsletter wants a list', E_USER_ERROR);
        }
        $this->list  = $list;
        $this->id    = $id;
        $this->error = new Intraface_Error;

        if ($this->id > 0) {
            $this->load();
        }
    }

    /**
     * Creates a newsletter from a kernel and id
     *
     * @param object  $kernel The kernel registry
     * @param integer $id     Newsletter id
     *
     * @return mixed
     */
    public static function factory($kernel, $id)
    {
        $db = new DB_Sql;
        $db->query("SELECT list_id FROM newsletter_archieve WHERE intranet_id = ".$kernel->intranet->get('id')." AND active = 1 AND id = ".intval($id));
        if ($db->nextRecord()) {
            $list   = new NewsletterList($kernel, $db->f('list_id'));
            $letter = new Newsletter($list, $id);
            return $letter;
        }
        trigger_error('Ugyldigt id', E_USER_ERROR);
        return false;
    }

    /**
     * Loads the information
     *
     * @return boolean
     */
    private function load()
    {
        $db = new DB_Sql;
         $db->query("SELECT id, list_id, subject, text, deadline, sent_to_receivers, status FROM newsletter_archieve WHERE id = " . $this->id . " AND active = 1 LIMIT 1");

        $db2 = new DB_Sql;
        if ($db->nextRecord()) {
            $this->value['id']                = $db->f('id');
            $this->value['list_id']           = $db->f('list_id');
            $this->value['subject']           = $db->f('subject');
            $this->value['text']              = $db->f('text');
            $this->value['deadline']          = $db->f('deadline');
            $this->value['sent_to_receivers'] = $db->f('sent_to_receivers');
            $this->value['status_key']        = $db->f('status');
            $this->value['status']            = $this->status[$db->f('status')];

            /*
            @todo Her skal vi lige have lavet noget status med hvor mange der modtager nyhedsbrevet
            $db2->query("SELECT id FROM email WHERE letter_id = " . $this->id. " AND intranet_id = " . $this->list->kernel->intranet->get('id'));
            $lettercount = $db->numRows();
            $db->query("SELECT id FROM newsletter_queue WHERE letter_id = " . $this->id . " AND status = 1  AND intranet_id = " . $this->list->kernel->intranet->get('id'));
            $lettersent = $db->numRows();
            if ($lettercount == 0) $status = 100; else $status = round($lettersent / $lettercount * 100);

            return( array('status' => $status, 'receivers' => $lettercount));
            $this->value['status'] = $status['status'];
            $this->value['receivers'] = $status['receivers'];
            */

        }
        return ($this->id = $db->f('id'));
    }

    /**
     * Deletes newsletter
     *
     * @return boolean
     */
    function delete()
    {
        if ($this->get('locked') == 1) {
            $this->error->set('Nyhedsbrevet er låst');
            return false;
        }
        $db = new DB_Sql;
        $db->query("UPDATE newsletter_archieve SET active = 0 WHERE id = " . $this->get("id") . "  AND intranet_id = " . $this->list->getIntranet()->getId() . " AND locked = 0");

        return true;
    }

    /**
     * Saves a newsletter
     *
     * @param struct $var Values to save
     *
     * @return integer of the newly created newsletter
     */
    function save($var)
    {
        $var = safeToDb($var);
        $var = array_map('strip_tags', $var);

        $validator = new Validator($this->error);
        $validator->isString($var['text'], 'Ugyldige tegn brug i tekst');
        $validator->isString($var['subject'], 'Ugyldige tegn brugt i emne');

        if ($this->error->isError()) {
            return 0;
        }

        if ($this->id == 0) {
            $sql_type = "INSERT INTO";
            $sql_end  = ', date_created = NOW()';
        } else {
            $sql_type = "UPDATE";
            $sql_end  = " WHERE id = " . $this->id;
        }
        $db  = new DB_Sql;
        $sql = $sql_type . " newsletter_archieve
            SET subject = '".$var['subject']."',
            text = '".$var['text']."',
            intranet_id = ".$this->list->getIntranet()->getId().",
            deadline = '".$var['deadline']."',
            list_id = ".$this->list->get('id');
        if (empty($var['deadline'])) {
            $sql .= ", deadline = NOW()";
        }
        $sql .= $sql_end;
        $db->query($sql);

        if ($this->id == 0) {
            $this->id = $db->insertedId();
        }
        $this->load();

        return $this->id;
    }

    /**
     * Sets that the newsletter has been sent, and to how many
     *
     * @param integer $receivers Number of receivers got the newsletter
     *
     * @return boolean
     */
    private function updateSent($receivers)
    {
        $db = new DB_Sql;
        $db->query("UPDATE newsletter_archieve SET status = 1, sent_to_receivers = '".(int)$receivers."' WHERE id = " . $this->id . " AND intranet_id = " . $this->list->getIntranet()->getId());
        return true;
    }

    /**
     * Get subscribers
     *
     * @return array with subscribers
     */
    function getSubscribers()
    {
        $subscriber = new NewsletterSubscriber($this->list);
        return $subscriber->getList();
    }

    /**
     * Puts subscribers in a queue to the newsletter
     *
     * @return boolean
     */
    function queue()
    {
        $subscribers = $this->getSubscribers();

        if ($this->get('sent') == 1) {
            $this->error->set('Nyhedsbrevet er allerede sendt');
            return false;
        }

        if (is_array($subscribers) AND count($subscribers) == 0) {
            $this->error->set('Ingen at sende nyhedsbrevet til');
            return false;
        }

        $validator = new Validator($this->error);
        $from      = $this->list->get('reply_email');
        $name      = $this->list->get('sender_name');
        $sql       = 'INSERT INTO email (date_created, date_updated, from_email, from_name, type_id, status, belong_to_id, date_deadline, intranet_id, contact_id, user_id, subject, body) VALUES ';
        $db        = MDB2::singleton(DB_DSN);

        if (PEAR::isError($db)) {
            die($result->getMessage() . $result->getUserInfo());
        }

        $i       = 0;
        $j       = 0;
        $skipped = 0;
        $params  = array();
        $error   = array();

        $subject = $db->quote($this->get('subject'), 'text');

        if (PEAR::isError($subject)) {
            die($subject->getUserInfo());
        }

        // TODO make escaping properly
        foreach ($subscribers AS $subscriber) {
            if (!$validator->isEmail($subscriber['contact_email'], "")) {
                $skipped++;
                continue;
            }

            $contact = $this->getContact($subscriber['contact_id']);

            $body = $db->quote($this->get('text')."\n\nLogin: ".$contact->getLoginUrl(), 'text');

            if (PEAR::isError($body)) {
                die($body->getUserInfo());
            }


            $params[] = "(
                NOW(),
                NOW(), '".$from."',
                '".$name."',
                8,
                2,
                ".$this->get('id') . ",
                '".$this->get('deadline'). "',
                " .$this->list->getIntranet()->getId(). " ,
                " .$subscriber['contact_id']. " ,
                " .$this->list->kernel->user->get('id').",
                ".$subject.",
                ".$body.")";

            if ($i == 40) {
                $result = $db->exec($sql . implode($params, ','));

                if (PEAR::isError($result)) {
                    $error[] = $result->getMessage() . $result->getUserInfo();
                    return false;
                }

                $params = array();
                $i      = 0;
            }

            $i++;
            $j++;
        }

        // If the number of contacts can be divided evenly into 40 there will be no more params here.
        if (count($params) > 0) {
           $result = $db->exec($sql . implode($params, ','));
        }

        if (PEAR::isError($result)) {
            $error[] = $result->getMessage() . $result->getUserInfo();
        }

        if (!empty($error)) {
            die(implode(' ', $error));
        }

        $this->updateSent($j);
        return true;
    }

    /**
     * Gets a contact
     *
     * @param integer $id Contact id
     *
     * @return object
     */
    function getContact($id)
    {
        return new Contact($this->list->kernel, $id);
    }

    /**
     * Gets all newsletters on a list
     *
     * @return array with newsletters
     */
    function getList()
    {
        $list = array();
        $db   = new DB_Sql;
        $db->query("SELECT * FROM newsletter_archieve WHERE active = 1 AND list_id = " . $this->list->get('id') . " ORDER BY deadline DESC");
        $i = 0;
        while ($db->nextRecord()) {

            $list[$i]['subject'] = $db->f('subject');
            $list[$i]['id']      = $db->f('id');

            $newsletter = new Newsletter($this->list, $db->f('id'));

            $list[$i]['status']            = $newsletter->get('status');
            $list[$i]['sent_to_receivers'] = $newsletter->get('sent_to_receivers');
            $i++;
        }
        return $list;
    }
}
