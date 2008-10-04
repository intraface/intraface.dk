<?php
ini_set('max_execution_time', 1200); // 20 min
require_once '../../../include_first.php';

// NOTICE IF INTRANET_ID IS CORRECT. 34 = carmakoma
if($kernel->intranet->getId() != 34) {
    die('Invalid intranet');
}

$html = file_get_contents(dirname(__FILE__) . '/email-nyhedsbrev2_uk.htm');
$text = 'Newsletter is supposed to be read as HTML.'; 
$crlf = "\r\n";
$hdrs = array(
              'From'    => 'info@carmakoma.com',
              'Subject' => 'carmakoma news'
              );
$mime = new Mail_mime($crlf);
$mime->setTXTBody($text);

$files = array(
    'email-pic10.jpg',
    'email-pic7.jpg',
    'email-pic8.jpg',
    'email-pic9.jpg',
    'hearts.gif',
    'logo-nyhed.jpg',
    'world.jpg'
);

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

$module = $kernel->module('contact');
$contact = new Contact($kernel);
$contacts = $contact->getList();

$date = date('YmdHis');

$i = 0;

if(!isset($_GET['send'])) {
    die('Du er nu klar til at sende. <a href="'.$_SERVER['PHP_SELF'].'?send=true">Klik her</a>');
}

$sent = array();
/*
$contacts = array();
$contacts[] = array('email' => 'lsolesen@gmail.com');
$contacts[] = array('email' => 'lsolesen@gmail.com');
$contacts[] = array('email' => 'lars@legestue.net');
*/
foreach ($contacts as $contact) {
    if (in_array($contact['email'], $sent)) {
        continue;
    }
    
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
    
    $sent[] = $contact['email'];
}

echo 'Count '.$i;
