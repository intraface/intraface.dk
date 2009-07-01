<?php
class Intraface_modules_shop_Controller_BasketEvaluation_Index extends k_Controller
{
    public $map = array('edit' => 'Intraface_modules_shop_Controller_BasketEvaluation_Edit');

    function getShop() {
        $doctrine = $this->registry->get('doctrine');
        return Doctrine::getTable('Intraface_modules_shop_Shop')->find($this->context->name);
    }

    function GET()
    {
        $shop = $this->getShop();

        $this->document->title = $this->__('Basket evaluation for') . ' ' . $shop->name;

        $this->document->options = array($this->url('../') => 'Close');

        $basketevaluation = new Intraface_modules_shop_BasketEvaluation($this->registry->get('db'), $this->registry->get('intranet'), $shop);
        $evaluations = $basketevaluation->getList();

        $data = array('shop' => $shop, 'evaluations' => $evaluations);

        return $this->render(dirname(__FILE__) . '/../tpl/evaluation-index.tpl.php', $data);
    }

    function forward($name)
    {
        if ($name == 'edit') {
            $next = new Intraface_modules_shop_Controller_BasketEvaluation_Edit($this, $name);
            return $next->handleRequest();
        } 

        $next = new Intraface_modules_shop_Controller_BasketEvaluation_Show($this, $name);
        return $next->handleRequest();
    }
}