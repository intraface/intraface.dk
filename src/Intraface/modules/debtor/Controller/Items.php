<?php
class Intraface_modules_debtor_Controller_Items extends k_Component
{
    function map($name)
    {
        return 'Intraface_modules_debtor_Controller_Item';
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getDebtor()
    {
        return $this->context->getDebtor();
    }
}
