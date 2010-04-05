<?php
class Intraface_modules_debtor_Controller_Payments extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function map($name)
    {
        return 'Intraface_modules_debtor_Controller_Payment';
    }

    function renderHtml()
    {
        $smarty = $this->template->create(dirname(__FILE__) . '/templates/payment');
        return $smarty->render($this);
    }

    function postForm()
    {
        $object = $this->getModel();
        $payment = $this->getPayment();
        if ($id = $payment->update($_POST)) {
             if ($this->getKernel()->user->hasModuleAccess('accounting')) {
                 return new k_SeeOther($this->url($id . '/state'));
             } else {
                 return new k_SeeOther($this->url('../'));
             }
        }
        return $this->render();
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getModel()
    {
        return $this->context->getModel();
    }

    function getPayment()
    {
        $object = $this->getModel();
        return $payment = new Payment($object);
    }

    function getType()
    {
        return $this->context->getType();
    }
}