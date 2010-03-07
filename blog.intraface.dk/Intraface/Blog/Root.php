<?php
class Intraface_Blog_Root extends k_Dispatcher
{
    public $map = array('cms' => 'IntrafacePublic_CMS_Controller_Index',);

    function __construct()
    {
        parent::__construct();
        //$this->document->template = dirname(__FILE__) . '/templates/main.tpl.php';
        $this->document->title = 'Intraface.dk';
        //$this->document->styles[] = $this->url('/style.css');
    }

    function execute()
    {
        throw new k_http_Redirect($this->url('cms'));
    }

    function getCMS()
    {
        return $this->registry->get('cms:client');
    }
}
