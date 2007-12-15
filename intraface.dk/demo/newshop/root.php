<?php
require_once 'controller.php';
class Demo_Shop_Root extends k_Dispatcher
{
    function __construct()
    {
        parent::__construct();
        $this->document->template = dirname(__FILE__) . '/main-tpl.php';
    }

    function GET()
    {
        return 'demo shop';
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
            throw new Exception('private key is not found for the intranet - shop cannot be generated');
        }

        $this->registry->set('shop', new IntrafacePublic_Shop_XMLRPC_Client(array("private_key" => $private_key, "session_id" => md5($this->registry->SESSION->getSessionId())), false, INTRAFACE_XMLPRC_SERVER_PATH . "shop/server3.php"));


        $next = new Demo_Shop_Controller($this, $name);
        return $next->handleRequest();
    }

}