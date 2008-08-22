<?php
ini_set('max_execution_time', 1200); // 20 min
require_once '../../include_first.php';

// NOTICE IF INTRANET_ID IS CORRECT. 34 = carmakoma
if($kernel->intranet->getId() != 34) {
    die('Invalid intranet');
}

$html = file_get_contents(dirname(__FILE__) . '/email-nyhedsbrev.htm');
$text = 'Vi fejrer lanceringen af carmakoma - Danmarks første high fashion tøjmærke for kurvede kvinder - med en release-reception. Dørene åbnes kl. 17.00 den 4. september 2008 på en hemmelig lokation i København. Send en e-mail til info@carmakoma.com med jeres navn senest tirsdag den 2. september 2008. Du får oplysning om stedet, når du tilmelder dig.'; 
$crlf = "\r\n";
$hdrs = array(
              'From'    => 'info@carmakoma.com',
              'Subject' => 'carmakoma release reception'
              );
$mime = new Mail_mime($crlf);
$mime->setTXTBody($text);

$files = array(
    'image.jpg'
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

if (PEAR::isError($mail)) {
    exit($mail->getMessage() . $mail->getUserInfo());
}

$module = $kernel->module('contact');
$contact = new Contact($kernel);
$contacts = $contact->getList();

$date = date('YmdHis');

$i = 0;

if(!isset($_GET['send'])) {
    die('Du er nu klar til at sende. <a href="https://www.intraface.dk/carmakoma/reception/send.php?send=true">Klik her</a>');
}

foreach ($contacts as $contact) {
    $hdrs['To'] = $contact['email'];
    
    // Only for not showing error when testing
    $result = new Intraface_Standard;
    
    
    // UNCOMMENT NEXT LINE TO SEND MESSAGES!
    $result = $mail->send($contact['email'], $hdrs, $body);
    //$result = $mail->send('lars@legestue.net', $hdrs, $body);
    
    if (!PEAR::isError($result)) {
        echo "sent to " . $contact['email'] . "<br />\n";
        $i++;
        file_put_contents(dirname(__FILE__).'/send'.$date.'.txt', $contact['email']."\n", FILE_APPEND);
    } else {
        echo "could NOT send to " . $contact['email'] . ": ".$result->getMessage().", ".$result->getUserInfo(). "<br />\n";   
    }
}

echo 'Count '.$i;
