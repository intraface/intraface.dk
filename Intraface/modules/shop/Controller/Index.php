<?php
class Intraface_modules_shop_Controller_Index extends k_Controller
{
    public $map = array('create' => 'Intraface_modules_shop_Controller_Edit');

    function GET()
    {
        return $this->render(dirname(__FILE__) . '/tpl/index.tpl.php');
    }

    function forward($name)
    {
        $next = new Intraface_modules_shop_Controller_Edit($this, $name);
        return $next->handleRequest();
    }
}