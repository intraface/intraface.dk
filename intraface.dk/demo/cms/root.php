<?php
require_once dirname(__FILE__) . '/controller.php';
class Demo_Cms_Root extends k_Dispatcher
{
    function __construct()
    {
        parent::__construct();
        $this->document->template = dirname(__FILE__) . '/main.tpl.php';
        $this->document->title = 'No title';
        //$this->document->styles[] = $this->url('/style.css');
    }

    function GET()
    {
        return 'no cms has been chosen yet';
    }

    function forward($name)
    {
        $client = $this->registry->get('admin');
        try {
            $private_key = $client->getPrivateKey('abcdefghijklmnopqrstuvwxyz123456789#', $name);
        } catch (Exception $e) {
            throw $e;
        }

        if (empty($private_key)) {
            throw new Exception('private key not found');
        }

        $debug = false;
        $site_id = 33;
        $this->registry->set('cms:client', new IntrafacePublic_CMS_XMLRPC_Client(array("private_key" => $private_key, "session_id" => md5($this->registry->SESSION->getSessionId())), $site_id, $debug, "http://localhost/intraface/intraface.dk/xmlrpc/cms/server2.php"));

        $next = new Demo_CMS_Controller($this, $name);
        return $next->handleRequest();
    }
}
