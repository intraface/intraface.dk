<?php

ini_set('max_execution_time', 1200); // 20 min
require_once '../../include_first.php';

die('disabled');

// NOTICE IF INTRANET_ID IS CORRECT. 34 = carmakoma
if($kernel->intranet->getId() != 34) {
    die('Invalid intranet');
}

$db = new DB_sql;

$module = $kernel->useModule('newsletter');

$list_dk = new NewsletterList($kernel, 24);
$subscriber_dk = new NewsletterSubscriber($list_dk);
$subscribers_dk = $subscriber_dk->getList();
$emails_dk = array();
foreach($subscribers_dk AS $s_dk) {
    $emails_dk[] = $s_dk['contact_email'];
}

echo 'DK: '.count($emails_dk)."<br />";

$list_uk = new NewsletterList($kernel, 23);
$subscriber_uk = new NewsletterSubscriber($list_uk);
$subscribers_uk = $subscriber_uk->getList();
$emails_uk = array();
foreach($subscribers_uk AS $s_uk) {
    $emails_uk[] = $s_uk['contact_email'];
}

echo 'UK: '.count($emails_uk)."<br />";

$module = $kernel->useModule('contact');
$contact = new Contact($kernel);
$contacts = $contact->getList();

echo 'Contacts: '.count($contacts)."<br />";

foreach($contacts AS $c) {
    
    $unique_contacts[$c['email']] = 1;
    
    if($c['number'] >= 160 && $c['number'] <= 272) {
        echo '.';
        if(!in_array($c['email'], $emails_dk) && !in_array($c['email'], $emails_uk)) {
            $contact = new Contact($kernel, $c['id']);
            
            echo 'Adding: '.$contact->getAddress()->get('email')."<br />";
            // $subscriber_dk->addContact($contact); 
        }
        else {
            echo 'Already in: '.$c['email']."<br />";
        }
    }
}

echo 'Unique: '.count($unique_contacts)."<br />";
?>
