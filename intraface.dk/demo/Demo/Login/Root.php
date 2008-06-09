<?php
class Demo_Login_Root extends k_Controller
{
    function GET()
    {
        return get_class($this) . ' has intentionally been left blank';
    }

    function getPrivateKey()
    {
        return $this->context->getPrivateKey();
    }

    function getCredentials()
    {
        $credentials = array();
        
        $credentials["private_key"] = $this->getPrivateKey();
        $credentials["session_id"] = md5(session_id());
        
        return $credentials;     
    }
    
    function getContactClient()
    {
        return new IntrafacePublic_Contact_XMLRPC_Client($this->getCredentials(), false, INTRAFACE_XMLPRC_SERVER_PATH . "contact/server.php");        
    }

    function getNewsletterClient()
    {
        return new IntrafacePublic_Newsletter_XMLRPC_Client($this->getCredentials(), false, INTRAFACE_XMLPRC_SERVER_PATH . "newsletter/server.php");        
    }
    
    function getDebtorClient()
    {
        return new IntrafacePublic_Debtor_XMLRPC_Client($this->getCredentials(), true, INTRAFACE_XMLPRC_SERVER_PATH . "debtor/server.php");
    }

    function forward($name)
    {
        $this->registry->set('contact', $this->getContactClient());
        $this->registry->set('newsletter', $this->getNewsletterClient());
        $this->registry->set('debtor', $this->getDebtorClient());
        $next = new IntrafacePublic_ContactLogin_Controller_Index($this, $name);
        return $next->handleRequest();
    }
}
