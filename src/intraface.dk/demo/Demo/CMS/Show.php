<?php
class Demo_CMS_Show extends k_Controller
{
    public $map = array('cms' => 'IntrafacePublic_CMS_Controller_Index');

    function GET()
    {
        return get_class($this) . ' intentionally left blank';
    }

    function getClient()
    {
        $shop_id = $this->name;

        $credentials = array("private_key" => $this->context->getPrivateKey(), 
                             "session_id" => md5($this->registry->get("k_http_Session")->getSessionId()));

        $debug = false;
        $site_id = $this->name;
        $client = new IntrafacePublic_CMS_Client_XMLRPC($credentials, $site_id, $debug, INTRAFACE_XMLPRC_SERVER_PATH . "cms/server2.php");
        $cms = new IntrafacePublic_CMS($client, $this->registry->get('cache'));
        
        return $cms;
    }

    function execute()
    {
        return $this->forward('cms');
    }
    
    function forward($name)
    {
        $this->registry->set('cms', $this->getClient());
        $next = new IntrafacePublic_CMS_Controller_Index($this, $name);
        return $next->handleRequest();
    }
    
    function getCMS()
    {
        return $this->registry->get('cms');
    }
}