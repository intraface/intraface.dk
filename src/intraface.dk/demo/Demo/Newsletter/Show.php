<?php
class Demo_Newsletter_Show extends k_Controller
{
    function GET()
    {
        return get_class($this) . ' intentionally left blank';
    }

    private function getCredentials()
    {
        return array("private_key" => $this->context->getPrivateKey(),
                     "session_id" => md5($this->registry->get("k_http_Session")->getSessionId()));
    }

    function getNewsletter()
    {
        $debug = false;
        $list_id = $this->name;
        $client = new IntrafacePublic_Newsletter_Client_XMLRPC(
            $this->getCredentials(),
            $list_id,
            $debug,
            INTRAFACE_XMLPRC_SERVER_PATH . "newsletter/server0101.php"); // , 'iso-8859-1', 'xmlrpcext'
        return $client;
    }

    function execute()
    {
        return $this->forward('list');
    }

    function forward($name)
    {
        $this->registry->set('newsletter', $this->getNewsletter());
        $next = new IntrafacePublic_Newsletter_Controller_Index($this, $name);
        return $next->handleRequest();
    }

}