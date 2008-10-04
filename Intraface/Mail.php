<?php
/**
 * Factory for returning the mailer class
 * 
 * @package Intraface
 * @author  Sune Jensen <sj@sunet.dk>
 * @author  Lars Olesen <lars@legestue.net>
 * @version @package-version@
 */

class Intraface_Mail
{
    
    public static function factory()
    {
        require_once 'phpmailer/class.phpmailer.php';
        $phpmailer = new Phpmailer;
        // opsætning
        $phpmailer->Mailer   = 'mail'; // Alternative to IsSMTP()
        // $phpmailer->isSMTP();//
        // $phpmailer->Host = SMTP_HOST; //smpt.domain.com
        // $phpmailer->Port = 25;//usually 25
        // $phpmailer->SMTPAuth = true;  // Auth Type
        // $phpmailer->Username = SMTP_USERNAME;
        // $phpmailer->Password = SMTP_PASSWORD;        
        // $phpmailer->SMTPKeepAlive = false; 
        $phpmailer->WordWrap = 75;
        $phpmailer->setLanguage('en');
        return $phpmailer;
    }
    
}
