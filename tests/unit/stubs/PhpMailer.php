<?php

class FakePhpMailer {
    
    public $Mailer;
    public $Wordwrap;
    public $From;
    public $FromName;
    public $Subject;
    public $Body;
    public $ErrorInfo;
    
    private $is_send;
    
    public function __construct()
    {
        $this->ErrorInfo = '';
        $this->is_send = false;
    }
    
    public function setLanguage($language) 
    {
        
    }
    
    public function addAddress($email, $name) 
    {
        
    }
    
    public function addBcc($email, $name) 
    {
        
    }
    
    public function addReplyTo($email) 
    {
        
    }
    
    public function addAttachment($file, $filename)
    {
        
    }
    
    public function send() 
    {
        $this->is_send = true;
    }
    
    public function clearAddresses()
    {
        
    }
    
    public function ClearReplyTos() 
    {
        
    }
    
    public function ClearAllRecipients()
    {
        
    }
    
    public function ClearAttachments()
    {
        
    }
    
    public function isSend()
    {
        return $this->is_send;
    }
}

?>