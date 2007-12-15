<?php
class Demo_Shop_Controller extends k_Controller
{
    public $map = array('shop' => 'IntrafacePublic_Shop_Controller_Index');

    function execute()
    {
        /*
        $client = $this->registry->get('admin');

        try {
            $private_key = $client->getPrivateKey('abcdefghijklmnopqrstuvwxyz123456789#', $this->name);
        } catch (Exception $e) {
            throw $e;
        }

        if (empty($private_key)) {
            throw new Exception('private key is not found for the intranet - shop cannot be generated');
        }

        $this->registry->set('shop', new IntrafacePublic_Shop_XMLRPC_Client(array("private_key" => $private_key, "session_id" => md5($this->registry->SESSION->getSessionId())), false, "http://www.intraface.dk/xmlrpc/shop/server3.php"));
        */
        return $this->forward('shop');
    }
}