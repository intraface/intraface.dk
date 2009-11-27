<?php
require 'common.php';

$mail = new Zend_Mail_Storage_Imap(array('host'     => 'mail.vih.dk',
                                         'user'     => 'lsolesen',
                                         'password' => 'klani'));

$analyzer = new Intraface_modules_newsletter_BounceAnalyzer;

foreach ($mail as $number => $message) {
    var_dump($message);

    /*
    if ($analyzer->isSoftBounce()) {
        continue;
    }

    if ($analyzer->isHardBounce()) {
        // remove from newsletter
        $mail->removeMessage($number);
    }



	*/
}

exit(1);
