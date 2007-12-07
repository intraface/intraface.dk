<?php
require_once 'controller.php';
class Demo_Root extends k_Dispatcher
{
    function __construct()
    {
        parent::__construct();
        $this->document->template = dirname(__FILE__) . '/main-tpl.php';
    }

    function forward($name)
    {
        $next = new Demo_Controller($this, $name);
        return $next->handleRequest();
    }

}