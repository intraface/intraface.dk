<?php
class Demo_CMS_Show extends k_Controller
{
    public $map = array('cms' => 'IntrafacePublic_CMS_Controller_Index');

    function GET()
    {
        return get_class($this) . ' intentionally left blank';
    }
    
    public function getPathToTemplate($template)
    {
        return 'Demo/CMS/standard-tpl.php';
    }

    public function getCMS()
    {
        $credentials = array("private_key" => $this->context->getPrivateKey(), 
                             "session_id" => md5($this->registry->get("k_http_Session")->getSessionId()));
        $debug = false;
        $client = new IntrafacePublic_CMS_Client_XMLRPC($credentials, $this->name, $debug, INTRAFACE_XMLPRC_SERVER_PATH . "cms/server2.php");
        return new IntrafacePublic_CMS($client, $this->registry->get('cache'));
         
    }
    
    public function forward($name)
    {
        if($name == 'enquiry') {
            $next = new IntrafacePublic_CMS_Controller_Enquiry($this, $name, 'secher@dsa-net.dk', 'sune.t.jensen@gmail.com');
        } else {
            $next = new IntrafacePublic_CMS_Controller_Index($this, $name);
        }
        return $next->handleRequest();
    }
}