<?php
class Demo_Root extends k_Dispatcher
{
    public $map = array('shop' => 'IntrafacePublic_Shop_Controller_Index');
    public $debug = true;
    public $i18n = array(
        'basket' => 'Indkøbskurv'
    );

    function __construct()
    {
        parent::__construct();
        $this->document->template = dirname(__FILE__) . '/main-tpl.php';
    }

    function execute()
    {
        throw new k_http_Redirect($this->url('shop'));
    }

}