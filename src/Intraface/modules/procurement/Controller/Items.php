<?php
class Intraface_modules_procurement_Controller_Items extends k_Component
{
    function map($name)
    {
        return 'Intraface_modules_procurement_Controller_Item';
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getProcurement()
    {
        return $this->context->getProcurement();
    }
}
