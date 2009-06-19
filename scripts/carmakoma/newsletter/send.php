<?php
ini_set('max_execution_time', 1200); // 20 min
require_once '../../include_first.php';

// NOTICE IF INTRANET_ID IS CORRECT. 34 = carmakoma
if ($kernel->intranet->getId() != 34) {
    die('Invalid intranet');
}

$html = file_get_contents(dirname(__FILE__) . '/email-nyhedsbrev.htm');
$text = 'Carmakoma.com launched today. Just so you know. This e-mail is supposed to be read as HTML.'; 
$crlf = "\r\n";
$hdrs = array(
              'From'    => 'info@carmakoma.com',
              'Subject' => 'Carmakoma launched'
              );
$mime = new Mail_mime($crlf);
$mime->setTXTBody($text);

$files = array(
    'email-pic1.jpg',
    'email-pic2.jpg',
    'email-pic3.jpg',
    'email-pic4.jpg',
    'email-pic5.jpg',
    'email-pic6.jpg',
    'logo6.jpg'
);

foreach ($files as $file) {
    $mime->addHTMLImage('./' . $file, "image/jpeg", $file, true);
}
$mime->setHTMLBody($html);
$body = $mime->get();
$hdrs = $mime->headers($hdrs);

$params["host"] = 'mail.legestue.net';
$params["port"] = 25;
$params["auth"] = true;
$params["username"] = 'lars@legestue.net';
$params["password"] = 'klaniklani';

$mail = Mail::factory("smtp", $params); 

$module = $kernel->module('contact');
$contact = new Contact($kernel);
$contacts = $contact->getList();

$date = date('YmdHis');

$i = 0;

if (time() < strtotime('2008-07-31 20:00:00')) {
    die('Du kan ikke sende mail før den 1. august 05:00');
}

if (!isset($_GET['send'])) {
    die('Du er nu klar til at sende. <a href="https://www.intraface.dk/carmakoma/newsletter/send.php?send=true">Klik her</a>');
}

foreach ($contacts as $contact) {
    $hdrs['To'] = $contact['email'];
    
    // Only for not showing error when testing
    $result = new Intraface_Standard;
    
    
    // UNCOMMENT NEXT LINE TO SEND MESSAGES!
    $result = $mail->send($contact['email'], $hdrs, $body);
    
    if (!PEAR::isError($result)) {
        echo "sent to " . $contact['email'] . "<br />\n";
        $i++;
        file_put_contents(dirname(__FILE__).'/send'.$date.'.txt', $contact['email']."\n", FILE_APPEND);
    } else {
        echo "could NOT send to " . $contact['email'] . ": ".$result->getMessage().", ".$result->getUserInfo(). "<br />\n";   
    }
}

echo 'Count '.$i;
