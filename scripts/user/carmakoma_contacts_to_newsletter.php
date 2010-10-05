<?php
/**
 * Used to find contacts in data interval, which has been created to the newsletter but not added to the newsletter.
 *
 * @author Sune Jensen
 */

die('used');

session_start();

define('INTRAFACE_K2', true);

require '../public_html/config.local.php';
require_once '../public_html/common.php';

require_once 'Intraface/modules/contact/Contact.php';
require_once 'Intraface/modules/newsletter/NewsletterSubscriber.php';
require_once 'Intraface/modules/newsletter/NewsletterList.php';

$kernel = new Intraface_Kernel();
$kernel->intranet = new Intraface_Intranet(34);
$kernel->setting = new Intraface_Setting(34);

$list = new NewsletterList($kernel, 24);

error_reporting(E_ALL);

$db = $bucket->get('MDB2_Driver_Common');

$result = $db->query('SELECT contact.id, contact.number, contact.date_created, address.email, address.name
    FROM contact
    LEFT JOIN address ON contact.id = address.belong_to_id AND address.type = 3 AND address.active = 1
    WHERE contact.active = 1 AND 
    contact.intranet_id = 34 AND
    (DATE_FORMAT(date_created, "%Y-%m-%d") = "2010-05-03" OR DATE_FORMAT(date_created, "%Y-%m-%d") = "2010-05-05" OR DATE_FORMAT(date_created, "%Y-%m-%d") = "2010-05-06")
    AND address.email != "" AND address.address = ""
    ');
$i = 0;

if (PEAR::isError($result)) {
    die($result->toString());
}

while ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC)) {
    echo $row['number']. ', ' . $row['id']. ', ' . $row['date_created'] . ', '.$row['email']. ', '. $row['name']. ', ';
    
    $newsletter = $db->query('SELECT id FROM newsletter_subscriber WHERE contact_id = '. $row['id']);
    if ($newsletter->numRows() == 0) {
        $subscriber = new NewsletterSubscriber($list);
        $contact = new Contact($kernel, $row['id']);
        
        // $subscriber->addContact($contact);
        // echo 'Contact added to danish newsletter';
    } else {
        echo 'Already in newsletter';
    }
    
    echo "\n";
    $i++;
}

echo $i. " number of contacts";

?>