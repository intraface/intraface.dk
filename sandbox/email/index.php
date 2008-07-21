<?php
require_once 'Ilib/ClassLoader.php';
$html = '<html><body><h1>Tester</h1><p>HTML version of email</p><img src="wideonball.jpg" alt="wideonball"></body></html>';
$crlf = "\n";
$hdrs = array(
              'From'    => 'lars@legestue.net',
              'Subject' => 'Test mime message'
              );
$mime = new Mail_mime($crlf);
$mime->setTXTBody($text);
$file = './wideonball.jpg';
$mime->addHTMLImage($file, "image/jpeg", 'wideonball.jpg', true);
$mime->setHTMLBody($html);
//$mime->addAttachment($file, "image/jpeg");
$body = $mime->get();
$hdrs = $mime->headers($hdrs);

$mail = Mail::factory('mail', $params);
$result = $mail->send('lsolesen@gmail.com', $hdrs, $body);
if (!PEAR::isError($result)) {
    exit('sent');
} else {
    exit ($result->getMessage());   
}