<?php
class Intraface_modules_shop_Controller_Show extends k_Component
{
    protected $mdb2;
    protected $template;

    function __construct(MDB2_Driver_Common $db, k_TemplateFactory $template)
    {
        $this->mdb2 = $db;
        $this->template = $template;
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getShopId()
    {
        return $this->name();
    }

    function renderHtml()
    {
        $shop = Doctrine::getTable('Intraface_modules_shop_Shop')->find($this->getShopId());

        $this->document->setTitle($shop->name);

        $this->document->options = array($this->url('../') => 'Close',
                                         $this->url('edit') => 'Edit',
                                         $this->url('featuredproducts') => 'Choose featured products',
                                         $this->url('categories') => 'Product categories',
                                         $this->url('basketevaluation') => 'Basket evaluation',
                                         $this->url('paymentmethods') => 'Payment methods');

        $basketevaluation = new Intraface_modules_shop_BasketEvaluation($this->mdb2, $this->getKernel()->intranet, $shop);
        $evaluations = $basketevaluation->getList();

        $data = array('shop' => $shop, 'evaluations' => $evaluations);
        $tpl = $this->template->create(dirname(__FILE__) . '/tpl/show');
        return $tpl->render($this, $data);
    }

    function map($name)
    {
        if ($name == 'edit') {
            return 'Intraface_modules_shop_Controller_Edit';
        } elseif ($name == 'basketevaluation') {
            return 'Intraface_modules_shop_Controller_BasketEvaluation_Index';
        } elseif ($name == 'featuredproducts') {
            return 'Intraface_modules_shop_Controller_FeaturedProducts';
        } elseif ($name == 'categories') {
            return 'Intraface_modules_shop_Controller_Categories';
        } elseif ($name == 'paymentmethods') {
            return 'Intraface_modules_shop_Controller_PaymentMethods_Index';
        }
    }
}