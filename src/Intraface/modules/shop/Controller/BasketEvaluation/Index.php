<?php
class Intraface_modules_shop_Controller_BasketEvaluation_Index extends k_Component
{
    public $map = array('edit' => 'Intraface_modules_shop_Controller_BasketEvaluation_Edit');

    protected $template;
    protected $mdb2;

    function __construct(k_TemplateFactory $template, MDB2_Driver_Common $mdb2)
    {
        $this->template = $template;
        $this->mdb2 = $mdb2;
    }

    function getShop()
    {
        return $this->context->getShop();
    }

    function renderHtml()
    {
        $shop = $this->getShop();

        $this->document->setTitle('Basket evaluation for' . ' ' . $shop->name);

        $this->document->options = array($this->url('../') => 'Close');

        $basketevaluation = new Intraface_modules_shop_BasketEvaluation($this->mdb2, $this->getKernel()->intranet, $shop);
        $evaluations = $basketevaluation->getList();

        $data = array('shop' => $shop, 'evaluations' => $evaluations);

        $tpl = $this->template->create(dirname(__FILE__) . '/../tpl/evaluation-index');
        return $tpl->render($this, $data);
    }

    function map($name)
    {
        if ($name == 'edit') {
            return 'Intraface_modules_shop_Controller_BasketEvaluation_Edit';
        }

        return 'Intraface_modules_shop_Controller_BasketEvaluation_Show';
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }
}