<?php
class Demo_Shop_Show extends k_Controller
{
    private $intranet_has_online_payment_access;
    public $map = array('shop' => 'IntrafacePublic_Shop_Controller_Index');
    private $translation = array();

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
        if ($this->intranet_has_online_payment_access === NULL) {
            $this->intranet_has_online_payment_access = $this->registry->get('admin')->hasModuleAccess($this->getCredentials(), 'onlinepayment');
        }
        return $this->intranet_has_online_payment_access;
    }

    function getShop()
    {
        $debug = false;
        $shop_id = $this->name;
        $client = new IntrafacePublic_Shop_Client_XMLRPC(
            $this->getCredentials(),
            $shop_id,
            $debug,
            INTRAFACE_XMLPRC_SERVER_PATH . "shop/server0100.php"); // 'iso-8859-1', 'xmlrpcext'
        return new IntrafacePublic_Shop($client, $this->registry->get('cache'));
    }

    public function getOnlinePayment()
    {
        $debug = false;
        if ($this->intranetHasOnlinePaymentAccess()) {
            return new IntrafacePublic_OnlinePayment(
                new IntrafacePublic_OnlinePayment_Client_XMLRPC(
                    $this->getCredentials(),
                    $debug,
                    INTRAFACE_XMLPRC_SERVER_PATH . "onlinepayment/server0100.php" // , 'iso-8859-1', 'xmlrpcext'
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

    /*
    public function getAvailableCountryRegions()
    {
        return 'Western Europe';
    }
    */

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

    /**
     * To test translations
     */
    /*
    function __($phrase)
    {
        if (empty($this->translation)) {

            $this->translation = new Ilib_Translation_Collection;

            $translator = Ilib_Countries_Translation::factory();
            $translator->setLang('da');
            $translator = $translator->getDecorator('UTF8');
            $this->translation->addTranslator($translator);

            $translator = IntrafacePublic_Shop_Translation::factory();
            $translator->setLang('da');
            $translator = $translator->getDecorator('DefaultText');
            $translator = $translator->getDecorator('UTF8');
            $this->translation->addTranslator($translator);

        }

        return $this->translation->get(utf8_encode($phrase));

    }
    */

}