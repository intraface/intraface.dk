<?php
class Intraface_modules_shop_Controller_BasketEvaluation_Show extends k_Component
{
    protected $mdb2;

    function __construct(MDB2_Driver_Common $mdb2)
    {
        $this->mdb2 = $mdb2;
    }

    function map($name)
    {
        if ($name == 'edit') {
            return 'Intraface_modules_shop_Controller_BasketEvaluation_Edit';
        } elseif ($name == 'delete') {
            return 'Intraface_modules_shop_Controller_BasketEvaluation_Delete';
        }
    }

    function dispatch()
    {
        $basketevaluation = new Intraface_modules_shop_BasketEvaluation($this->mdb2, $this->context->getKernel()->intranet, $this->getShop(), (int)$this->name());
        if ($basketevaluation->getId() == 0) {
            throw new k_PageNotFound();
        }

        return parent::dispatch();
    }

    function renderHtml()
    {
        return 'No content on this page!';
    }

    function renderHtmlDelete()
    {
        $basketevaluation = new Intraface_modules_shop_BasketEvaluation($this->mdb2, $this->context->getKernel()->intranet, $this->getShop(), (int)$this->name());
        $basketevaluation->delete();

        return new k_SeeOther($this->url('../'));
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getShop()
    {
        return $this->context->getShop();
    }
}