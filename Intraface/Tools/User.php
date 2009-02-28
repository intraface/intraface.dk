<?php
class Intraface_Tools_User
{
    private $session;

    function __construct($session)
    {
        $this->session = &$session->get('tools');
    }

    function login($user, $password)
    {
        if (md5($password) != 'b0e8b7a28fc9ba7255869b58fb992f71') {
            return false;
        }
   
        $this->session['logged_in'] = true;    
        
        return true;
    }

    function isLoggedIn()
    {
        if ($this->session['logged_in'] != true) {
            return false;
        }
        return true;
    }
}