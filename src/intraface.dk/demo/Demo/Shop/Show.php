<?php
class Demo_Shop_Show extends k_Component
{
    private $intranet_has_online_payment_access;
    public $map = array('shop' => 'IntrafacePublic_Shop_Controller_Index');
    private $translation = array();
    protected $client;
    protected $cache;
    protected $template;

    function __construct(IntrafacePublic_Admin_Client_XMLRPC $client, k_TemplateFactory $template, Cache_Lite $cache)
    {
        $this->client = $client;
        $this->template = $template;
        $this->cache = $cache;
    }

    function map($name)
    {
        return 'IntrafacePublic_Shop_Controller_Index';
    }

    function renderHtml()
    {
        return 'Intentionally left blank';
    }

    function forward($class_name, $namespace = '')
    {
        $next = new IntrafacePublic_Shop_Controller_Index($this->getShop(), $this->template);
        $next->setContext($this);
        $next->setUrlState($this->url_state);
        $next->setDocument($this->document);
        $next->setComponentCreator($this->component_creator);
        //$next->setTranslatorLoader($this->translator_loader);
        $next->setDebugger($this->debugger);

        return $next->dispatch();
    }

    private function getCredentials()
    {
        return array("private_key" => $this->context->getPrivateKey(),
                     "session_id" => md5($this->session()->sessionId()));
    }

    private function intranetHasOnlinePaymentAccess()
    {
        if ($this->intranet_has_online_payment_access === NULL) {
            $this->intranet_has_online_payment_access = $this->client->hasModuleAccess($this->getCredentials(), 'onlinepayment');
        }
        return $this->intranet_has_online_payment_access;
    }

    function getShop()
    {
        $debug = false;
        $shop_id = $this->name();
        $client = new IntrafacePublic_Shop_Client_XMLRPC(
            $this->getCredentials(),
            $shop_id,
            $debug,
            INTRAFACE_XMLPRC_SERVER_PATH . "shop/server0100.php",
            'utf-8'); // 'iso-8859-1', 'xmlrpcext'
        return new IntrafacePublic_Shop($client, $this->cache);
    }

    public function getOnlinePayment()
    {
        $debug = false;
        if ($this->intranetHasOnlinePaymentAccess()) {
            return new IntrafacePublic_OnlinePayment(
                new IntrafacePublic_OnlinePayment_Client_XMLRPC(
                    $this->getCredentials(),
                    $debug,
                    INTRAFACE_XMLPRC_SERVER_PATH . "onlinepayment/server0100.php", // , 'iso-8859-1', 'xmlrpcext'
                    'utf-8'
                ),
                $this->cache
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
}