<?php

//
$list_id = 24; /* 23: UK, 24: DK */

// The html file
$newsletter_html = 'newsletter-dec-08-dk.htm'; /* NB: language */

// The files that should be attachecd
$files = array(
    'newsletter-base.jpg',
    'newsletter-header2.jpg', /* NB: language */
    'newsletter-mainbg-dk.jpg', /* NB: language */
    'shopnow-button.jpg', 
);

// The subject
$subject = 'Den perfekte kjole...'; /* NB: language */


ini_set('max_execution_time', 1200); // 20 min
require_once '../../../include_first.php';

// NOTICE IF INTRANET_ID IS CORRECT. 34 = carmakoma
if($kernel->intranet->getId() != 34) {
    die('Invalid intranet');
}

$html = file_get_contents(dirname(__FILE__) . '/'.$newsletter_html);
$text = 'Newsletter is supposed to be read as HTML.';
$crlf = "\r\n";
$hdrs = array(
              'From'    => 'Carmakoma <info@carmakoma.com>',
              'Subject' => $subject
);

$mime = new Mail_mime($crlf);
$mime->setTXTBody($text);

foreach ($files as $file) {
    $mime->addHTMLImage('./' . $file, "image/jpeg", $file, true);
}
$mime->setHTMLBody($html);
$body = $mime->get();
$hdrs = $mime->headers($hdrs);

$params["host"] = 'mail.dev.intraface.dk';
$params["port"] = 25;
$params["auth"] = true;
$params["username"] = 'smtp@dev.intraface.dk';
$params["password"] = 'ED!gt@g';

$mail = Mail::factory("smtp", $params);

$module = $kernel->module('newsletter');

$list = new NewsletterList($kernel, $list_id);
$subscriber = new NewsletterSubscriber($list);
$contacts = $subscriber->getList();

// For testing;

// $contacts = array();
// $contacts[] = array('contact_email' => 'lsolesen@gmail.com');
// $contacts[] = array('contact_email' => 'lars@legestue.net');
// $contacts[] = array('contact_email' => 'sj@sunet.dk');
// $contacts[] = array('contact_email' => 'admin@nylivsstil.dk');


echo 'List: '.$list->get('title')."<br />";
echo 'Number of recepients: '.count($contacts)."<br />";
echo 'File to send: '.$newsletter_html."<br />";
echo 'Images to include: '.implode(', ', $files)."<br />";

$date = date('YmdHis');

$i = 0;

if(!isset($_GET['send'])) {
    die('Du er nu klar til at sende. <a href="'.$_SERVER['PHP_SELF'].'?send=true">Klik her</a>');
}

$sent = array();


foreach ($contacts as $contact) {
    if (in_array($contact['contact_email'], $sent)) {
        continue;
    }

    $hdrs['To'] = $contact['contact_email'];

    // 27/10 2008 $contact['contact_login_url'] was added to the contacts array, so
    // when a update is made after this date the login url can be added.
    // Maybe like this:
    // $body_contact = str_replace('##login_url##', $contact['contact_login_url'], $body)

    // Only for not showing error when testing
    $result = new Intraface_Standard;

    // UNCOMMENT NEXT LINE TO SEND MESSAGES!
    $result = $mail->send($contact['contact_email'], $hdrs, $body);

    if (!PEAR::isError($result)) {
        echo "sent to " . $contact['contact_email'] . "<br />\n";
        $i++;
        file_put_contents(dirname(__FILE__).'/send'.$date.'.txt', $contact['contact_email']."\n", FILE_APPEND);
    } else {
        echo "could NOT send to " . $contact['contact_email'] . ": ".$result->getMessage().", ".$result->getUserInfo(). "<br />\n";
    }

    $sent[] = $contact['contact_emaill'];
}

echo 'Count '.$i;
