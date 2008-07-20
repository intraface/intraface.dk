<?php
class Intraface_modules_shop_Controller_Index extends k_Controller
{
    public $map = array('create' => 'Intraface_modules_shop_Controller_Edit');

    function GET()
    {
        $this->document->title = $this->__('Shops');
        $this->document->options = array($this->url('create') => 'Create');

        $doctrine = $this->registry->get('doctrine');
        $shops = Doctrine::getTable('Intraface_modules_shop_Shop')->findByIntranetId($this->registry->get('kernel')->intranet->getId());

        if (count($shops) == 0) {
            return $this->render(dirname(__FILE__) . '/tpl/empty-table.tpl.php', array('message' => 'No shops has been created yet.'));    
        }
    

        
        $data = array('shops' => $shops);
        return $this->render(dirname(__FILE__) . '/tpl/shops.tpl.php', $data);
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