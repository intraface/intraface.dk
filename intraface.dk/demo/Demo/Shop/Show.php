<?php
class Demo_Shop_Show extends k_Controller
{
    public $map = array('shop' => 'IntrafacePublic_Shop_Controller_Index');

    function GET()
    {
        return get_class($this) . ' intentionally left blank';
    }

    function getShop()
    {
        $shop_id = $this->name;

        $credentials = array("private_key" => $this->context->getPrivateKey(),
                             "session_id" => md5($this->registry->get("k_http_Session")->getSessionId()));
        $client = new IntrafacePublic_Shop_Client_XMLRPC2($credentials, $shop_id, false, INTRAFACE_XMLPRC_SERVER_PATH . "shop/server0004.php");
        return new IntrafacePublic_Shop($client, $this->registry->get('cache'));
    }

    function execute()
    {
        return $this->forward('shop');
    }

    function forward($name)
    {
        $this->registry->set('shop', $this->getShop());
        $next = new IntrafacePublic_Shop_Controller_Index($this, $name);
        return $next->handleRequest();
    }

}