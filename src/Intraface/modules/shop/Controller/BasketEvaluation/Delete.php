<?php
class Intraface_modules_shop_Controller_BasketEvaluation_Delete extends k_Component
{
    protected $mdb2;

    function __construct(MDB2_Driver_Common $mdb2)
    {
        $this->mdb2 = $mdb2;
    }

    function getShop()
    {
        return $this->context->getShop();
    }

    function renderHtml()
    {
        $basketevaluation = new Intraface_modules_shop_BasketEvaluation($this->mdb2, $this->context->getKernel()->intranet, $this->getShop(), (int)$this->context->name());
        if ($basketevaluation->getId() == 0) {
            throw new exception('Invalid basket evaluation '.$this->context->name());
        }
        $basketevaluation->delete();

        return new k_SeeOther($this->url('../../'));

    }
}