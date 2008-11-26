<?php

//
$list_id = 23; /* 23: UK, 24: DK */

ini_set('max_execution_time', 1200); // 20 min
require_once '../../../include_first.php';

// NOTICE IF INTRANET_ID IS CORRECT. 34 = carmakoma
if($kernel->intranet->getId() != 34) {
    die('Invalid intranet');
}


$module = $kernel->module('newsletter');

$list = new NewsletterList($kernel, $list_id);
$subscriber = new NewsletterSubscriber($list);
$subscriber->setDBQuery(new Intraface_DBQuery($list->kernel, "newsletter_subscriber", "newsletter_subscriber.list_id=". $list->get("id") . " AND newsletter_subscriber.intranet_id = " . $list->kernel->intranet->get('id') . " AND newsletter_subscriber.active = 1 AND newsletter_subscriber.optin = 0"));

$contacts = $subscriber->getList();

// For testing;




foreach ($contacts as $contact) {
    echo $contact['contact_email'] . "\n";
    $i++;
    
}

echo 'Count '.$i;
