<?php
/**
 * NewsletterSubscriber
 *
 * This class handles the subscribers to the the different lists.
 *
 * @category  Intraface
 * @package   Intraface_Newsletter
 * @author    Lars Olesen <lars@legestue.net>
 * @version   @package-version@
 */
require_once 'Intraface/functions.php';
require_once 'Intraface/modules/contact/Contact.php';
require_once 'Intraface/shared/email/Email.php';

class NewsletterSubscriber extends Intraface_Standard
{
    public $list; //object
    public $value;
    public $error;
    public $contact;
    public $id;
    private $dbquery;
    private $observers = array();

    /**
     * Constructor
     *
     * @param object  $list List object
     * @param integer $id   Subscriber id
     *
     * @return void
     */
    public function __construct($list, $id = 0)
    {
        $this->error = new Intraface_Error;

        if (!is_object($list)) {
            trigger_error('subscriber Kræver en liste', E_USER_ERROR);
        }

        $this->list = $list;

        $this->id = (int)$id;

        if ($this->id > 0) {
            $this->load();
        }
    }

    /**
     * @return DBQuery object
     */
    public function getDBQuery()
    {
        if ($this->dbquery) {
            return $this->dbquery;
        }
        // optin = 1 should not be set here
        $this->dbquery = new Intraface_DBQuery($this->list->kernel, "newsletter_subscriber", "newsletter_subscriber.list_id=". $this->list->get("id") . " AND newsletter_subscriber.intranet_id = " . $this->list->kernel->intranet->get('id'));
        $this->dbquery->useErrorObject($this->error);
        $this->dbquery->setFilter('optin', 1);
        $this->dbquery->setFilter('active', 1);
        $this->dbquery->setSorting('date_submitted DESC');
        return $this->dbquery;
    }

    /**
     * Starter NewsletterSubscriber ud fra alt andet end list
     *
     * @todo Skal laves til at have følgende parameter: $kernel, $from_what (code, email, id), $id
     *
     * @param object $object Different objects
     * @param string $type   What type to create the object from
     * @param string $value  Which value should be connected to the type
     *
     * @return object
     */
    public function factory($object, $type, $value)
    {
        switch ($type) {
            case 'code':
                // kernel og kode
                $code = trim($value);
                $code = mysql_escape_string($code);
                $code = strip_tags($code);

                $db = new DB_Sql;
                $db->query("SELECT id, list_id FROM newsletter_subscriber WHERE code = '".$code."' AND intranet_id = " . $object->intranet->get('id')." and active = 1");
                if (!$db->nextRecord()) {
                    return false;
                }

                return new NewsletterSubscriber(new NewsletterList($object, $db->f('list_id')), $db->f('id'));

            break;

            case 'email':
                // email og list
                $email = safeToDb($value);
                $db = new DB_Sql;
                $db->query("SELECT newsletter_subscriber.id
                    FROM newsletter_subscriber
                    LEFT JOIN contact
                        ON newsletter_subscriber.contact_id = contact.id
                    LEFT JOIN address
                        ON address.belong_to_id = contact.id
                    WHERE address.email = '".$email."'
                        AND newsletter_subscriber.list_id = " . $object->get('id') . "
                        AND newsletter_subscriber.intranet_id = " . $object->kernel->intranet->get('id') . "
                        AND newsletter_subscriber.active = 1
                        AND contact.active = 1");
                if (!$db->nextRecord()) {
                    return 0;
                }

                return new NewsletterSubscriber($object, $db->f('id'));

            break;

            default:
                trigger_error('NewsletterSubscriber::factory: Ulovlig Type');
            break;
        }
    }

    /**
     * @return boolean
     */
    private function load()
    {
        $db = new DB_Sql;
        $db->query("SELECT * FROM newsletter_subscriber WHERE id = " . $this->id." and active = 1");
        if (!$db->nextRecord()) {

            $this->id = 0;
            $this->value['id'] = 0;
            return 0;
        }

        $this->value['id'] = $db->f('id');
        $this->value['contact_id'] = $db->f('contact_id');
        $this->value['code'] = $db->f('code');
        $this->contact = new Contact($this->list->kernel, $db->f('contact_id'));
        $this->value['email'] = $this->contact->get('email');
        $this->value['resend_optin_email_count'] = $db->f('resend_optin_email_count');

        return 1;
    }

    /**
     * @return boolean
     */
    public function delete()
    {
        $db = new DB_Sql;
        $db->query('UPDATE newsletter_subscriber SET active = 0 WHERE id = ' . $this->id);
        return true;
    }

    /**
     * @param integer $contact_id Contact id
     *
     * @return Contact object
     */
    public function getContact($contact_id)
    {
        // $contact_module = $this->list->kernel->getModule('contact', true); // true: tjekker kun intranet_access
        require_once 'Intraface/modules/contact/Contact.php';
        return new Contact($this->list->kernel, $contact_id);
    }

    /**
     * Adds an existing contact
     *
     * @param integer $contact_id Contact id
     *
     * @return integer of the id of the subscriber
     */
    public function addContact($contact)
    {
        $db = new DB_sql;

        $db->query("SELECT id FROM newsletter_subscriber WHERE contact_id = '".$contact->getId()."' AND list_id = " . $this->list->get("id") . " AND intranet_id = ".$this->list->kernel->intranet->get('id')." AND active = 1");
        if ($db->nextRecord()) {
            return $db->f('id');
        }

        // Spørgsmålet er om vedkommende bør få en e-mail, hvor man kan acceptere?
        $db->query("INSERT INTO newsletter_subscriber SET
                    contact_id = '".$contact->getId()."',
                    list_id = " . $this->list->get("id") . ",
                    date_submitted=NOW(),
                    optin = 1,
                    code = '".md5($this->list->get("id") . $this->list->kernel->intranet->get('id') . date('Y-m-d H:i:s') . $contact->getId())."',
                    intranet_id = ".$this->list->kernel->intranet->get('id'));

        return $db->insertedId();
    }

    /**
     * IMPORTANT: To comply with spam legislation we must save which date it is submitted and the ip.
     *
     * @param struct $input With all values
     *
     * @return boolean
     */
    public function subscribe($input, $mailer)
    {
        if (!is_object($mailer)) {
            throw new Exception('A valid mailer object is needed');
        }

        $input = safeToDb($input);
        $input = array_map('strip_tags', $input);

        $validator = new Intraface_Validator($this->error);
        $validator->isEmail($input['email'], $input['email'] . ' er ikke en gyldig e-mail');

        if (empty($input['name'])) {
            $input['name'] = $input['email'];
        }

        if (!empty($input['name'])) {
            $validator->isString($input['name'], 'Der er brugt ulovlige tegn i navnet', '', 'allow_empty');
        }

        if ($this->error->isError()) {
            return false;
        }

        // Det er smartere hvis vi bare loader fra e-mail
        $db = new DB_Sql;

        // jeg kan dog ikke få lov at reassigne i php5 - så hvad skal jeg gøre i stedet?
        $which_subscriber_has_email = NewsletterSubscriber::factory($this->list, 'email', $input['email']);
        if (is_object($which_subscriber_has_email)) {
            $this->id = $which_subscriber_has_email->get('id');
        }
        $this->load();

        if ($this->id > 0) {
            if ($this->get('contact_id') == 0) {
                $contact = Contact::factory($this->list->kernel, 'email', $input['email']);
            } else {
                $contact = new Contact($this->list->kernel, $this->get('contact_id'));
            }
            // because of the NewsletterSubscriber::factory($, 'email') we should be sure there actually is a valid contact. But maybe we should do a check anyway.

            /*
            if (!$contact->get('name')) {
                $save_array['name'] = $input['name'];
            }

            $save_array['email'] = $input['email'];

            if (!$contact_id = $contact->save($save_array)) {
                $contact->error->view();
                $this->error->set('Kunne ikke gemme kontaktpersonen');
            }
            */
            // name og e-mail bør vel ikke nødv. gemmes?

            $db->query("UPDATE newsletter_subscriber
                SET
                    contact_id = '".$contact->get('id')."',
                    name='".$input['name']."',
                    email = '".$input['email']."',
                    date_submitted = NOW(),
                    ip_submitted = '".$input['ip']."'
                WHERE id = ".$this->id."
                    AND list_id = " . $this->list->get("id") . "
                    AND intranet_id = " . $this->list->kernel->intranet->get('id'));
            //code =  '" . md5($input['email'] . date('Y-m-d H:i:s') . $input['ip'])."'

        } else {
            $contact = Contact::factory($this->list->kernel, 'email', $input['email']);

            if ($contact->get('id') == 0) {
                if (empty($input['name'])) {
                    $name = $input['email'];
                } else {
                    $name = $input['name'];
                }
                if (!$contact_id = $contact->save(array('name' => $name, 'email' => $input['email']))) {
                    //$contact->error->view();
                    $this->error->set('Kunne ikke gemme kontaktpersonen');
                }
            }

            $db->query("INSERT INTO newsletter_subscriber
                SET
                    contact_id = '".$contact->get('id')."',
                    email = '".$input['email']."',
                    name='".$input['name']."',
                    list_id = " . $this->list->get("id") . ",
                    ip_submitted='".$input['ip']."',
                    date_submitted=NOW(),
                    code= '" . md5($input['email'] . date('Y-m-d H:i:s') . $input['ip'])."',
                    intranet_id = ".$this->list->kernel->intranet->get('id'));

        }

        if ($this->id == 0) {
            $this->id = $db->insertedId();
        }

        // sender kun optinbrev, hvis man ikke er opted in
        if (!$this->optedIn()) {

            // TODO replace by observer
            if (!$this->sendOptInEmail($mailer)) {
                $this->error->set('could not send optin email');
                return false;
            }

            // $this->notifyObservers('new subscriber');
        }


        return true;
    }

    /**
     * Checks whether subscriber has opted in yet
     *
     * @return boolean
     */
    public function optedIn()
    {
        if ($this->id == 0) {
            return false;
        }
        $db = new DB_Sql;
        $db->query("SELECT * FROM newsletter_subscriber WHERE id = " . $this->id." and active = 1");
        if (!$db->nextRecord()) {
            return false;
        }

        if ($db->f('optin') == 0) {
            return false;
        }
        return true;
    }

    /**
     * Deletes the user from a newsletter list
     *
     * IMPORTANT: The user must be deleted, not just deactivated.
     *
     * @param string $email Email
     *
     * @return boolean
     */
    public function unsubscribe($email)
    {
        $email = strip_tags($email);

        $validator = new Intraface_Validator($this->error);
        $validator->isEmail($email, 'E-mailen er ikke gyldig');

        if ($this->error->isError()) {
            return 0;
        }

        $which_subscriber_has_email = NewsletterSubscriber::factory($this->list, 'email', $email);
        if (is_object($which_subscriber_has_email)) {
            $this->id = $which_subscriber_has_email->get('id');
        }
        $this->load();

        $db = new DB_Sql;
        $db->query("UPDATE newsletter_subscriber SET active = 0, date_unsubscribe = '".date('Y-m-d H:i:s')."' WHERE id=".$this->id." AND list_id = " . $this->list->get("id") . " AND intranet_id = " . $this->list->kernel->intranet->get('id'));
        return true;
    }

    /**
     * IMPORTANT: To comply with spam legislation we must save date_optin and ip_optin.
     *
     * @param string $code Optin code
     * @param string $ip   IP
     *
     * @return boolean
     */
    public function optIn($code, $ip)
    {
        $db = new DB_Sql;
        $db->query("SELECT id FROM newsletter_subscriber WHERE code = '".$code."' AND list_id = " . $this->list->get('id')." AND active = 1");
        if (!$db->nextRecord()) {
            return false;
        }

        $db->query("UPDATE newsletter_subscriber SET optin = 1, ip_optin = '".$ip."', date_optin = NOW() WHERE code = '" . $code . "' AND list_id = " . $this->list->get('id')." AND active = 1");

        // makes sure that the submitted ip is also set - not really a port of this method.
        $db->query("SELECT id, ip_submitted FROM newsletter_subscriber WHERE code = '".$code."' AND list_id = " . $this->list->get('id'));
        if ($db->nextRecord()) {
            if (!$db->f('ip_submitted')) {
                $db->query("UPDATE newsletter_subscriber SET ip_submitted = '".$ip."' WHERE id = " . $db->f("id"));
            }
        }
        return true;
    }

    /**
     * The subscriber must receive an e-mail so the subscribtion can be confirmed
     * The e-mail should say that the subscription should be confirmed within a week.
     *
     * E-mailen skal indeholde følgende:
     * - url til privacy policy på sitet
     * - en kort beskrivelse af mailinglisten
     * - url som brugeren følger for at bekræfte tilmeldingen
     *
     * - I virkeligheden skal den nok nøjes med lige at logge ind i ens personlige webinterface
     *   hvor man så kan lave bekræftelsen fra. Det skal altså bare være loginkoden fra
     *   den personlige konto, der står der, og så skal nyhedsbreve på forsiden (hvis dette sted
     *   har nogle nyhedsbreve).
     *
     * @see tilføj cleanUp();
     *
     * @return boolean
     */
    public function sendOptInEmail($mailer)
    {
        if (!is_object($mailer)) {
            throw new Exception('A valid mailer object is needed');
        }

        if ($this->id == 0) {
            $this->error->set('no id');
            return false;
        }

        // @todo hack for legacy purposes, could also just update the db
        $subscribe_subject = $this->list->get('subscribe_subject');
        if (empty($subscribe_subject)) {
            $subscribe_subject = 'Bekræft tilmelding';
        }

        $this->load();

        $contact = new Contact($this->list->kernel, $this->get('contact_id'));

        // @todo should probably also introduce some kind of greeting setting in list
        $email = new Email($this->list->kernel);
        $data = array(
                'subject' => $subscribe_subject,
                'body' =>
                    $this->list->get('subscribe_message') . "\n\n" .
                    $this->getLoginUrl($contact) .
                    "\n\n".$this->list->get('sender_name'),
                'contact_id' => $this->get('contact_id'),
                'from_email' => $this->list->get('reply_email'),
                'from_name' => $this->list->get('sender_name'),
                'type_id' => 7, // nyhedsbreve
                'belong_to' => $this->list->get('id')
            );

        if (!$email->save($data)) {
            $this->error->set('could not send the e-mail' . implode(',', $email->error->messages));
            return false;
        }

        if ($email->send($mailer)) {
            $db = new DB_Sql;
            $db->query("UPDATE newsletter_subscriber SET date_optin_email_sent = NOW() WHERE id = " . $this->id);
            return true;
        }
        $this->error->set('could not send the e-mail' . implode(',', $email->error->message));
        return false;
    }

    /**
     * Resends the optin e-mail to the user again, and adds one to count of resend times.
     *
     */
    function resendOptInEmail($mailer)
    {
        if($this->sendOptInEmail($mailer)) {
            $db = new DB_Sql;
            $db->query("UPDATE newsletter_subscriber SET resend_optin_email_count = resend_optin_email_count + 1 WHERE id = " . $this->id);
            return true;
        }
    }

    private function getLoginUrl($contact)
    {
        if (!$link = $this->list->get('optin_link')) {
            return $contact->getLoginUrl() . '&optin=' . $this->get('code');
        }
        return $link . '?optin=' . $this->get('code');
    }

    /**
     * gets a list
     *
     * @return boolean
     */
    public function getList()
    {
        $subscribers = array();

        //$db = new DB_Sql;
        //$db->query("SELECT id, contact_id, date_submitted, DATE_FORMAT(date_submitted, '%d-%m-%Y') AS dk_date_submitted FROM newsletter_subscriber WHERE list_id=". $this->list->get("id") . " AND intranet_id = " . $this->list->kernel->intranet->get('id') . " AND optin = 1 AND active = 1");
        $i = 0;
        $this->getDBQuery()->setCondition('newsletter_subscriber.optin = '.$this->getDBQuery()->getFilter('optin'));
        $this->getDBQuery()->setCondition('newsletter_subscriber.active = '.$this->getDBQuery()->getFilter('active'));

        $db = $this->getDBQuery()->getRecordset("id, date_optin_email_sent, contact_id, resend_optin_email_count, date_submitted, DATE_FORMAT(date_submitted, '%d-%m-%Y') AS dk_date_submitted, optin", "", false);

        while ($db->nextRecord()) {
            $contact_id = $db->f('contact_id');
            $subscribers[$i]['id'] = $db->f('id');
            $subscribers[$i]['contact_id'] = $db->f('contact_id');
            $subscribers[$i]['dk_date_submitted'] = $db->f('dk_date_submitted');
            $subscribers[$i]['date_submitted'] = $db->f('date_submitted');
            $subscribers[$i]['date_optin_email_sent'] = $db->f('date_optin_email_sent');
            $subscribers[$i]['optin'] = $db->f('optin');
            $subscribers[$i]['resend_optin_email_count'] = $db->f('resend_optin_email_count');

            if (isset($this->list->kernel->user)) { // only if we are logged in.
                $contact = $this->getContact($db->f('contact_id'));
                $subscribers[$i]['contact_number'] = $contact->get('number');
                $subscribers[$i]['contact_name'] = $contact->address->get('name');
                $subscribers[$i]['contact_address'] = $contact->address->get('address');
                $subscribers[$i]['contact_postcode'] = $contact->address->get('postcode');
                $subscribers[$i]['contact_city'] = $contact->address->get('city');
                $subscribers[$i]['contact_email'] = $contact->address->get('email');
                $subscribers[$i]['contact_country'] = $contact->address->get('country');
                $subscribers[$i]['contact_login_url'] = $contact->getLoginUrl();
            }
            $i++;
        }

        $db->free();

        // vi skal have result free
        return $subscribers;
    }

    /**
     * @param object $observer Must implement an update() method
     */
    public function addObserver($observer)
    {
        $this->observers[] = $observer;
    }

    /**
     * @return array with observers
     */
    public function getObservers()
    {
        return $this->observers;
    }

    /**
     * @param string $state Of this object
     */
    public function notifyObservers($state)
    {
        foreach ($this->getObservers() AS $observer) {
            $observer->update($this, $state);
        }
    }
}