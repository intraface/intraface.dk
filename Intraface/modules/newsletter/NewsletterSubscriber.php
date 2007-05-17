<?php
/**
 * NewsletterSubscriber
 *
 * This class handles the subscribers to the the different lists.
 *
 * @category  Intraface
 * @package   Newsletter
 * @author    Lars Olesen <lars@legestue.net>
 * @version   @package-version@
 */

require_once 'Intraface/Standard.php';
require_once 'Intraface/DBQuery.php';
require_once 'Intraface/functions/functions.php';
require_once 'Intraface/modules/contact/Contact.php';
require_once 'Intraface/shared/email/Email.php';

class NewsletterSubscriber extends Standard {

    var $list; //object
    var $value;
    var $error;
    var $contact;
    var $id;
    var $dbquery;
    private $observers = array();

    /**
     * @param object  $list List object
     * @param integer $id   Subscriber id
     *
     * @return void
     */
    function __construct($list, $id = 0)
    {
        $this->error = new Error;

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
    function createDBQuery()
    {
        $this->dbquery = new DBQuery($this->list->kernel, "newsletter_subscriber", "newsletter_subscriber.list_id=". $this->list->get("id") . " AND newsletter_subscriber.intranet_id = " . $this->list->kernel->intranet->get('id') . " AND newsletter_subscriber.optin = 1 AND newsletter_subscriber.active = 1");
        $this->dbquery->useErrorObject($this->error);
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
    function factory($object, $type, $value)
    {
        switch ($type) {
            case 'code':
                // kernel og kode
                $code = trim($value);
                $code = mysql_escape_string($code);
                $code = strip_tags($code);

                $db = new DB_Sql;
                $db->query("SELECT id, list_id FROM newsletter_subscriber WHERE code = '".$code."' AND intranet_id = " . $object->intranet->get('id'));
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
    function load()
    {
        $db = new DB_Sql;
        $db->query("SELECT * FROM newsletter_subscriber WHERE id = " . $this->id);
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

        return 1;
    }

    /**
     * @return boolean
     */
    function delete()
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
    function getContact($contact_id)
    {
        $contact_module = $this->list->kernel->getModule('contact', true); // true: tjekker kun intranet_access
        return new Contact($this->list->kernel, $contact_id);
    }

    /**
     * Adds an existing contact
     *
     * @param integer $contact_id Contact id
     *
     * @return integer of the id of the subscriber
     */
    function addContact($contact_id)
    {
        $this->list->kernel->useModule('contact');
        //$this->list->kernel->useModule('contact');
        $contact = new Contact($this->list->kernel, (int)$contact_id);


        if ($contact->get('id') == 0) {
            $this->error->set("Ugyldig kontakt");
            return 0;
        }

        $db = new DB_sql;

        $db->query("SELECT id FROM newsletter_subscriber WHERE contact_id = '".$contact_id."' AND list_id = " . $this->list->get("id") . " AND intranet_id = ".$this->list->kernel->intranet->get('id')." AND active = 1");
        if ($db->nextRecord()) {
            $this->error->set("Kontakten er allerede tilføjet");
            return 0;
        }

        //
        // Spørgsmålet er om vedkommende bør få en e-mail, hvor man kan acceptere?
        //

        $db->query("INSERT INTO newsletter_subscriber SET
                    contact_id = '".$contact_id."',
                    list_id = " . $this->list->get("id") . ",
                    date_submitted=NOW(),
                    optin = 1,
                    code = '".md5($this->list->get("id") . $this->list->kernel->intranet->get('id') . date('Y-m-d H:i:s') . $contact_id)."',
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
    function subscribe($input)
    {
        $input = safeToDb($input);
        $input = array_map('strip_tags', $input);

        $validator = new Validator($this->error);
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
                if (!$contact_id = $contact->save(array('name' => $input['email'], 'email' => $input['email']))) {
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
            /*
            if (!$this->sendOptInEmail()) {
                $this->error->set('could not send optin email');
                return false;
            }
            */
            $this->notifyObservers('new subscriber');
        }

        return true;
    }

    /**
     * Checks whether subscriber has opted in yet
     *
     * @return boolean
     */
    function optedIn()
    {
        if ($this->id == 0) {
            return 0;
        }
        $db = new DB_Sql;
        $db->query("SELECT * FROM newsletter_subscriber WHERE id = " . $this->id);
        if (!$db->nextRecord()) {
            return 0;
        }

        if (!$db->f('ip_optin')) {
            return 0;
        }
        return 1;
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
    function unsubscribe($email)
    {
        $email = strip_tags($email);

        $validator = new Validator($this->error);
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
        $db->query("DELETE FROM newsletter_subscriber WHERE id=".$this->id." AND list_id = " . $this->list->get("id") . " AND intranet_id = " . $this->list->kernel->intranet->get('id'));
        return 1;
    }

    /**
     * IMPORTANT: To comply with spam legislation we must save date_optin and ip_optin.
     *
     * @param string $code Optin code
     * @param string $ip   IP
     *
     * @return boolean
     */
    function optIn($code, $ip) {
        /*
        if ($this->id == 0) {
            return 0;
        }
        */
        $db = new DB_Sql;
        $db->query("UPDATE newsletter_subscriber SET optin = 1, ip_optin = '".$ip."', date_optin = NOW() WHERE code = '" . $code . "' AND list_id = " . $this->list->get('id'));

        $db->query("SELECT id, ip_submitted FROM newsletter_subscriber WHERE code = '".$code."' AND list_id = " . $this->list->get('id'));
        if ($db->nextRecord()) {
            if (!$db->f('ip_submitted')) {
                $db->query("UPDATE newsletter_subscriber SET ip_submitted = '".$ip."' WHERE id = " . $db->f("id"));
            }
        }
        return 1;
    }

    /**
     * @return integer
     */
    function getSubscriberCount()
    {
        $db = new DB_Sql("SELECT * FROM newsletter WHERE list_id=".$this->list->get('id') . " AND intranet_id = " . $this->list->kernel->intranet->get('id') . " AND optin = 1");
        return $db->numRows();
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
    function sendOptInEmail()
    {
        if ($this->id == 0) {
            $this->error->set('no id');
            return false;
        }

        $this->load();

        $contact = new Contact($this->list->kernel, $this->get('contact_id'));

        $email = new Email($this->list->kernel);
        $data = array(
                'subject' => 'Bekræft tilmelding',
                'body' =>
                    $this->list->get('subscribe_message') . "\n\n" .
                    $contact->get('login_url') .
                    "\n\nMed venlig hilsen\n".$this->list->get('sender_name'),
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

        if ($email->send()) {
            $db = new DB_Sql;
            $db->query("UPDATE newsletter_subscriber SET date_optin_email_sent = NOW() WHERE id = " . $this->id);
            return true;
        }
        $this->error->set('could not send the e-mail' . implode(',', $email->error->message));
        return false;
    }

    /**
     * @todo - den her får virkelig kørt nogle sql'er :) - det skal reduceres
     *
     * @return boolean
     */
    function getList()
    {
        $subscribers = array();

        //$db = new DB_Sql;
        //$db->query("SELECT id, contact_id, date_submitted, DATE_FORMAT(date_submitted, '%d-%m-%Y') AS dk_date_submitted FROM newsletter_subscriber WHERE list_id=". $this->list->get("id") . " AND intranet_id = " . $this->list->kernel->intranet->get('id') . " AND optin = 1 AND active = 1"); // optin = 1 hvad er det? /Sune
        $i = 0;

        $db = $this->dbquery->getRecordset("id, contact_id, date_submitted, DATE_FORMAT(date_submitted, '%d-%m-%Y') AS dk_date_submitted", "", false);

        while ($db->nextRecord()) {
            $contact_id = $db->f('contact_id');
            $subscribers[$i]['id'] = $db->f('id');
            $subscribers[$i]['contact_id'] = $db->f('contact_id');
            $subscribers[$i]['dk_date_submitted'] = $db->f('dk_date_submitted');
            $subscribers[$i]['date_submitted'] = $db->f('date_submitted');

            if (isset($this->list->kernel->user)) { // only if we are logged in.
                $contact = $this->getContact($db->f('contact_id'));
                $subscribers[$i]['contact_number'] = $contact->get('number');
                $subscribers[$i]['contact_name'] = $contact->address->get('name');
                $subscribers[$i]['contact_address'] = $contact->address->get('address');
                $subscribers[$i]['contact_postcode'] = $contact->address->get('postcode');
                $subscribers[$i]['contact_city'] = $contact->address->get('city');
                $subscribers[$i]['contact_email'] = $contact->address->get('email');
                $subscribers[$i]['contact_country'] = $contact->address->get('country');
            }
            $i++;
        }

        $db->free();

        // vi skal have result free
        return $subscribers;
    }

    /**
     * This function must clean up the list for non-confirmed subscriptions
     * This method should delete unconfirmed subscriptions which
     * are more than a week old.
     *
     * @return boolean
     */
    function cleanUp()
    {
        die('Ikke implementeret');
    }

    /**
     * @param object $observer Must implement an update() method
     */
    function addObserver($observer)
    {
        $this->observers[] = $observer;
    }

    /**
     * @return array with observers
     */
    function getObservers()
    {
        return $this->observers;
    }

    /**
     * @param string $state Of this object
     */
    function notifyObservers($state)
    {
        foreach ($this->getObservers() AS $observer) {
            $observer->update($this, $state);
        }
    }
}
?>