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
$result = $db->query("SELECT name, public_key, id FROM intranet WHERE id = 1");
require_once 'Intraface/modules/contact/Contact.php';
require_once 'Intraface/modules/newsletter/NewsletterSubscriber.php';
require_once 'Intraface/modules/newsletter/NewsletterList.php';
$i = 0;
$db        = MDB2::singleton(DB_DSN);
while ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC)) {

    $auth_adapter = new Intraface_Auth_PublicKeyLogin(MDB2::singleton(DB_DSN), md5(session_id()), $row['public_key']);
    $weblogin = $auth_adapter->auth();
        
    if (!$weblogin) {
        throw new Exception('Access to the intranet denied. The private key is probably wrong.');
    } 

    $kernel = new Intraface_Kernel();
    $kernel->intranet = new Intraface_Intranet($weblogin->getActiveIntranetId());
    $kernel->setting = new Intraface_Setting($kernel->intranet->get('id'));

    if (!$kernel->intranet->hasModuleAccess('newsletter')) {
        continue;
    }

    $res = $db->query("SELECT * FROM newsletter_subscriber WHERE optin = 0 AND active = 1 AND intranet_id = " . $row['id']);
    while ($r = $res->fetchRow()) {

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

        $subject = 'Bekræftelse på tilmelding';
        $body = "Du modtager denne e-mail fordi vi har omlagt vores nyhedsbrevssystem, og vi har brug for din bekræftelse på, at du gerne vil modtage følgende nyhedsbrev:\n\n" . $subscriber->list->get('description') . "\n\nKlik på nedenstående link, hvis du vil modtage nyhedsbrevet:\n\n" . $url;

        $sql       = 'INSERT INTO email (date_created, date_updated, from_email, from_name, type_id, status, belong_to_id, date_deadline, intranet_id, contact_id, user_id, subject, body) VALUES ';


        if (PEAR::isError($db)) {
            throw new Exception($result->getMessage() . $result->getUserInfo());
        }

        $i       = 0;
        $j       = 0;
        $skipped = 0;
        $params  = array();
        $error   = array();

            $params[] = "(
                NOW(),
                NOW(), '".$from."',
                '".$name."',
                8,
                2,
                0,
                '".date('Y-m-d H:i:s'). "',
                " .$subscriber->list->getIntranet()->getId(). " ,
                " .$contact->getId(). " ,
                0,
                ".$subject.",
                ".$body.")";

            if ($i == 40) {
                //$result = $db->exec($sql . implode($params, ','));
                print $sql . implode($params, ',');

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
    $res->free();


}
echo 'written to ' . $i;
exit;
?>