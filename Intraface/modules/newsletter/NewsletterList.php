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

class NewsletterList extends Standard {

    var $value;
    var $id;
    var $kernel;
    var $error;
    // var $subscription_option_types;

    /**
     * Kan det være, at den selv skulle kunne starte kernel op?
     */

    function NewsletterList($kernel, $id = 0) {
        if (!is_object($kernel) OR strtolower(get_class($kernel)) != 'kernel') {
            trigger_error('Listadministration kræver Kernel', E_USER_ERROR);
        }
        $this->kernel = $kernel;

        $newsletter_module = $this->kernel->getModule('newsletter');
        // $this->subscribe_option_types = $newsletter_module->getSetting('subscribe_option');

        $this->id = (int)$id;
        if ($this->id > 0) {
            $this->load();
        }
        $this->error = new Error;
    }


    /**
     * Denne skal sikkert kræve Kernel også, så det hele er lukket ind i Kernel. Undersøg lige om det ikke er rigtigt.
     */
    function load() {
        $db = new DB_Sql;
        $db2 = new DB_Sql;
        $db->query("SELECT * FROM newsletter_list WHERE active = 1 AND id = " . $this->id . " AND intranet_id = " . $this->kernel->intranet->get('id'));
        if (!$db->nextRecord()) {
            return 0;
        }

        $this->value['id'] = $db->f('id');
        $this->value['title'] = $db->f('title');
        $this->value['description'] = $db->f('description');
        //$this->value['subscribe_option_key'] = $db->f('subscribe_option_key');
        //$this->value['subscribe_option'] = $this->subscribe_option_types[$db->f('subscribe_option_key')];
        $this->value['subscribe_message'] = $db->f('subscribe_message');
        $this->value['unsubscribe_message'] = $db->f("unsubscribe_message");
        //$this->value['optin_link'] = $this->kernel->setting->get('intranet', 'newsletter.optin_link');
        $this->value['privacy_policy'] = $db->f('privacy_policy');
        $this->value['sender_name'] = $db->f('sender_name');
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

        return 1;
    }

    function getList() {
        $lists = array();
        $db = new DB_Sql;
        $db2 = new DB_Sql;
        $db->query("SELECT * FROM newsletter_list WHERE intranet_id = " . $this->kernel->intranet->get('id')." AND active = 1");
        $i = 0;
        while ($db->nextRecord()) {
            $list = new Newsletterlist($this->kernel, $db->f('id'));
            $lists[$i]['id'] = $db->f('id');
            $lists[$i]['title'] = $db->f('title');
            $lists[$i]['subscribers'] = $list->get('subscribers');
            //$lists[$i]['subscribe_option_key'] = $db->f('subscribe_option_key');
            //$lists[$i]['subscribe_option'] = $this->subscribe_option_types[$db->f('subscribe_option_key')];


            $i++;
        }
        return $lists;
    }

    function validate($var) {
        $validator = new Validator($this->error);

        $validator->isString($var['title'], "Titel er ikke ufdyldt korrekt");
        $validator->isString($var['sender_name'], "Navn på afsender er ikke ufdyldt korrekt", "", "allow_empty");
        $validator->isEmail($var['reply_email'], "Svar E-mail er ikke en gyldig e-mail", "allow_empty");
        //$validator->isUrl($var['privacy_policy'], "Privatlivspolitisk er ikke en gyldig webadresse", "allow_empty");
        /*
        $validator->isNumeric($var['subscribe_option_key'], "Tilmeldingsmuligheder er ikke et tal", "zero_or_creater");
        if(!isset($this->subscribe_option_types[$var['subscribe_option_key']])) {
            $this->error->set("Tilmeldingsmuligheder er ikke en gyldig mulighed");
        }
        */
        $validator->isString($var['description'], 'Beskrivelse er ikke gyldig', "<strong><em>", "allow_empty");
        $validator->isString($var['subscribe_message'], "Bekræftelse på tilmelding er ikke udfyldt korrekt", '', "allow_empty");
        //$validator->isString($var['unsubscribe_message'], "Frameldingsbesked er ikke udfyldt korrekt", '', "allow_empty");

        if($this->error->isError()) {
            return 0;
        }
        return 1;
    }


    /**
     * Gemmer oplysninger om listen
     */
    function save($var) {

        $var = safeToDb($var);

        if (!$this->validate($var)) {
            return 0;
        }

        $db = new DB_Sql;
        // privacy_policy = \"".$var['privacy_policy']."\",
        //	unsubscribe_message = \"".$var['unsubscribe_message']."\",

        $sql = "sender_name = \"".$var['sender_name']."\",
            reply_email = \"".$var['reply_email']."\",
            description = \"".$var['description']."\",
            title = \"".$var['title']."\",

            subscribe_message = \"".$var['subscribe_message']."\"";
            // subscribe_option_key = ".$var['subscribe_option_key'].",

        if ($this->id > 0) {
            $db->query("UPDATE newsletter_list SET ".$sql.", date_changed = NOW() WHERE id = " . $this->id);
        }
        else {
            $db->query("INSERT INTO newsletter_list SET ".$sql.", intranet_id = " . $this->kernel->intranet->get('id').", date_created = NOW(), date_changed = NOW()");
            $this->id = $db->insertedId();
        }

        return $this->id;
    }

    /**
     * TODO Bør kun kunne slette lister hvor der ikke er breve der ikke er sendt
     */
    function delete() {
        $db = new DB_Sql;
        $db->query("UPDATE newsletter_list SET active = 0 WHERE id = " . $this->id . " AND intranet_id = " . $this->kernel->intranet->get('id'));

        /*$db->query("SELECT * FROM newsletter_arhieve WHERE list_id = " . $this->get("id") . " AND intranet_id = " . $this->kernel->intranet->get('id'));
        while ($db->nextRecord()) {
            $newsletter = new Newsletter($db->f("id"));
            if ($newsletter->get('is_sent') == 1) {
                $this->error->set('Nyhedsbrev er afsendt. Listen kan ikke slettes.');
            }
            else {
                $newsletter->delete();
            }
        }
        if ($this->error->isError()) {
            return 0;
        }
        $db->query("DELETE FROM newsletter_list WHERE id = " . $this->id . " AND intranet_id = " . $this->kernel->intranet->get('id'));
        return 1;
        */
    }

    function doesListExist() {
        $db = new DB_Sql;
        $db->query("SELECT id FROM newsletter_list WHERE id = " . $this->id . " AND intranet_id = " . $this->kernel->intranet->get('id'));
        return $db->numRows();
    }

}
?>