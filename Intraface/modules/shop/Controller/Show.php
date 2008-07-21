<?php
class Intraface_modules_shop_Controller_Show extends k_Controller
{
    public $map = array('edit' => 'Intraface_modules_shop_Controller_Edit',
                        'basketevaluation' => 'Intraface_modules_shop_Controller_EvaluationEdit',
                        'featuredproducts' => 'Intraface_modules_shop_Controller_FeaturedProducts',
                        'categories' => 'Intraface_modules_shop_Controller_Categories',
                        'paymentmethods' => 'Intraface_modules_shop_Controller_PaymentMethods_Index');

    function getShopId()
    {
        return $this->name;
    }

    function GET()
    {
        $doctrine = $this->registry->get('doctrine');
        $shop = Doctrine::getTable('Intraface_modules_shop_Shop')->find($this->getShopId());

        $this->document->title = $shop->name;

        $this->document->options = array($this->url('../') => 'Close',
                                         $this->url('edit') => 'Edit',
                                         $this->url('featuredproducts') => 'Choose featured products',
                                         $this->url('categories') => 'Product categories',
                                         $this->url('basketevaluation') => 'Basket evaluation',
                                         $this->url('paymentmethods') => 'Payment methods');

        $basketevaluation = new Intraface_modules_shop_BasketEvaluation($this->registry->get('db'), $this->registry->get('intranet'), $shop);
        $evaluations = $basketevaluation->getList();

        $data = array('shop' => $shop, 'evaluations' => $evaluations);

        return $this->render(dirname(__FILE__) . '/tpl/show.tpl.php', $data);
    }

    function forward($name)
    {
        if ($name == 'edit') {
            $next = new Intraface_modules_shop_Controller_Edit($this, $name);
            return $next->handleRequest();
        } elseif ($name == 'basketevaluation') {
            $next = new Intraface_modules_shop_Controller_BasketEvaluation_Index($this, $name);
            return $next->handleRequest();
        } elseif ($name == 'featuredproducts') {
            $next = new Intraface_modules_shop_Controller_FeaturedProducts($this, $name);
            return $next->handleRequest();
        } elseif ($name == 'categories') {
            $next = new Intraface_modules_shop_Controller_Categories($this, $name);
            return $next->handleRequest();
        } elseif ($name == 'paymentmethods') {
            $next = new Intraface_modules_shop_Controller_PaymentMethods_Index($this, $name);
            return $next->handleRequest();
        }

        throw new Exception('Unknown forward');
    }
}