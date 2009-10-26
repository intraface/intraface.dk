<?php
class Intraface_modules_debtor_Controller_Typenegotiator extends k_Component
{
    function map($name)
    {
        if ($name == 'list') {
            return 'Intraface_modules_debtor_Controller_Collection';
        }
    }

    function getType()
    {
        return $this->name();
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function renderHtml()
    {
        return new k_SeeOther($this->url('list'));
    }
}