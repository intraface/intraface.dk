<?php
class Intraface_modules_shop_Controller_BasketEvaluation_Show extends k_Component
{
    function getShop()
    {
        return $this->context->getShop();
    }

    function renderHtml()
    {
        /*
        $basketevaluation = new Intraface_modules_shop_BasketEvaluation($this->registry->renderHtml('db'), $this->getKernel()->intranet, $this->getShop(), $this->name());
        if ($basketevaluation->getId() == 0) {
            throw new Exception('Invalid basket evaluation '.$this->name());
        }
        */
        return 'No content on this page!';
    }

    function map($name)
    {
        if ($name == 'edit') {
            return 'Intraface_modules_shop_Controller_BasketEvaluation_Edit';
        } elseif ($name == 'delete') {
            return 'Intraface_modules_shop_Controller_BasketEvaluation_Delete';
        }
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

}