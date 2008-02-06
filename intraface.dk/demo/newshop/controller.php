<?php
class Demo_Shop_Controller extends k_Controller
{
    public $map = array('shop' => 'IntrafacePublic_Shop_Controller_Index');

    function execute()
    {
        return $this->forward('shop');
    }
}