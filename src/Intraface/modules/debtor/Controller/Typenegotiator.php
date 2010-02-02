<?php
class Intraface_modules_debtor_Controller_Typenegotiator extends k_Component
{
    function map($name)
    {
        if ($name == 'list') {
            return 'Intraface_modules_debtor_Controller_Collection';
        }
    }

    function dispatch()
    {
        $this->url_state->set('contact_id', $this->query('contact_id'));
        return parent::dispatch();
    }

    function renderHtml()
    {
        return new k_SeeOther($this->url('list'));
    }

    function getType()
    {
        return $this->name();
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }
}