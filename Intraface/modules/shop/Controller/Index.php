<?php
class Intraface_modules_shop_Controller_Index extends k_Controller
{
    public $map = array('create' => 'Intraface_modules_shop_Controller_Edit');

    function GET()
    {
        $doctrine = $this->registry->get('doctrine');

        $shops = Doctrine::getTable('Intraface_modules_shop_Shop')->findAll();

        $data = array('shops' => $shops);

        return $this->render(dirname(__FILE__) . '/tpl/index.tpl.php', $data);
    }

    function forward($name)
    {
        if ($name == 'create') {
            $next = new Intraface_modules_shop_Controller_Edit($this, $name);
            return $next->handleRequest();
        }
        $next = new Intraface_modules_shop_Controller_Show($this, $name);
        return $next->handleRequest();

    }
}