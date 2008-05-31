<?php
/**
 * cronjob som skal sende e-mails for nyhedsbrevsudsenderen.
 *
 * VIGTIGT:
 * Dreamhost har et maksimalt antal afsendte e-mails på en time på 150.
 * Derfor må der ikke sendes flere end det.
 *
 * Det styres ved at cronjobbet kun sættes i gang en gang i timen - og
 * at der kun sendes 125 e-mails ad gangen.
 *
 * @author Lars Olesen <lars@legestue.net>
 */

session_start();

require 'config.local.php';
require_once 'common.php';

error_reporting(E_ALL);

$db = MDB2::singleton(DB_DSN);
$db->setFetchMode(MDB2_FETCHMODE_ASSOC);
$result = $db->query("SELECT name, public_key, id FROM intranet");
require_once 'Intraface/modules/contact/Contact.php';
require_once 'Intraface/modules/newsletter/NewsletterSubscriber.php';
require_once 'Intraface/modules/newsletter/NewsletterList.php';
$i = 0;
while ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC)) {

    $kernel = new Intraface_Kernel();
    if (!$kernel->weblogin('public', $row['public_key'], md5(session_id()))) {
        trigger_error('could not login', E_USER_ERROR);
    }

    if (!$kernel->intranet->hasModuleAccess('newsletter')) {
        continue;
    }

    $res = $db->query("SELECT * FROM newsletter_subscriber WHERE optin = 0 AND active = 1 AND intranet_id = " . $row['id']);
    while ($r = $res->fetchRow()) {

        //$contact = new Contact($kernel, $r['contact_id']);
        //echo $contact->getId();
        //echo $contact->getLoginUrl() . '&optin=' . $r['code'];
        echo $r['list_id'] . ' - ' . $r['contact_id'] . ' - ';
        $subscriber = NewsletterSubscriber::factory($kernel, 'code', $r['code']);
        if (!is_object($subscriber)) {
            continue;
        }
        if ($subscriber->list->get('optin_link')) {
            $url = $subscriber->list->get('optin_link') . '?optin=' . $r['code'];
        } else {
            $contact = new Contact($kernel, $r['contact_id']);
            $url = $contact->getLoginUrl() . '&optin=' . $r['code'];
        }
        echo $url;
        echo '<br>';
        $i++;

    }
    $res->free();


    //$email = new Email($kernel);
    //$email->sendAll();

}
echo 'written to ' . $i;
exit;
?>