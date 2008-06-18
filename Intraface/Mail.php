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
        $phpmailer->WordWrap = 75;
        $phpmailer->setLanguage('en', 'phpmailer/language/');
        return $phpmailer;
    }
    
}
