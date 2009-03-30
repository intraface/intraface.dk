<?php
class Demo_Shop_Show extends k_Controller
{
    private $intranet_has_online_payment_access;
    public $map = array('shop' => 'IntrafacePublic_Shop_Controller_Index');

    function GET()
    {
        return get_class($this) . ' intentionally left blank';
    }
    
    private function getCredentials()
    {
        return array("private_key" => $this->context->getPrivateKey(),
                             "session_id" => md5($this->registry->get("k_http_Session")->getSessionId()));
    }
    
    private function intranetHasOnlinePaymentAccess()
    {
        if($this->intranet_has_online_payment_access === NULL) {
            $this->intranet_has_online_payment_access = $this->registry->get('admin')->hasModuleAccess($this->getCredentials(), 'onlinepayment');
        }
        return $this->intranet_has_online_payment_access;
    }

    function getShop()
    {
        $shop_id = $this->name;
        $client = new IntrafacePublic_Shop_Client_XMLRPC2($this->getCredentials(), $shop_id, false, INTRAFACE_XMLPRC_SERVER_PATH . "shop/server0004.php");
        return new IntrafacePublic_Shop($client, $this->registry->get('cache'));
    }
    
    public function getOnlinePayment()
    {
            
        if ($this->intranetHasOnlinePaymentAccess()) {
            return new IntrafacePublic_OnlinePayment(
                new IntrafacePublic_OnlinePayment_Client_XMLRPC(
                    $this->getCredentials(),
                    false,
                    INTRAFACE_XMLPRC_SERVER_PATH . "onlinepayment/server0002.php"
                ),
                $this->registry->get("cache")
            );
        }
        return false;
    }
    
    public function getOnlinePaymentAuthorize()
    {        
        if ($this->intranetHasOnlinePaymentAccess()) {       
            return new Ilib_Payment_Authorize_Provider_Testing("12345678", "fakequickpaymd5secret");
        }
        return false;
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