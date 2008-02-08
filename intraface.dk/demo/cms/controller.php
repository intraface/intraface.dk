<?php
class Demo_CMS_Controller extends k_Controller
{
    public $map = array('cms' => 'IntrafacePublic_CMS_Controller_Index');

    function execute()
    {
        throw new k_http_Redirect($this->url('cms'));
    }
}
