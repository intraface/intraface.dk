<?php
class Demo_CMS_Show extends k_Component
{
    private $map = array('cms' => 'IntrafacePublic_CMS_Controller_Index');
    protected $cache;

    function __construct(Cache_Lite $cache)
    {
        $this->cache = $cache;
    }

    public function map($name)
    {
        if ($name == 'enquiry') {
            return 'IntrafacePublic_CMS_Controller_Enquiry';
        } else {
            return 'IntrafacePublic_CMS_Controller_Index';
        }
    }

    function renderHtml()
    {
        return get_class($this) . ' intentionally left blank';
    }

    public function getPathToTemplate($template)
    {
        return 'Demo/CMS/standard';
    }

    public function getCMS()
    {
        $credentials = array(
        	"private_key" => $this->context->getPrivateKey(),
            "session_id" => md5($this->session()->sessionId()));
        $debug = true;
        $client = new IntrafacePublic_CMS_Client_XMLRPC($credentials, $this->name(), $debug, INTRAFACE_XMLPRC_SERVER_PATH . "cms/server0300.php");
        return new IntrafacePublic_CMS($client, $this->cache);
    }
}