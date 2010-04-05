<?php
class Intraface_modules_debtor_Controller_Payment extends k_Component
{
    protected $template;
    private $payment;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function map($name)
    {
        return 'Intraface_modules_accounting_Controller_State_Payment';
    }

    function renderHtml()
    {
        $smarty = $this->template->create(dirname(__FILE__) . '/templates/payment');
        return $smarty->render($this);
    }

    function postForm()
    {
        $payment = $this->getModel();
        if ($id = $payment->update($_POST)) {
            if ($this->getKernel()->user->hasModuleAccess('accounting')) {
                return new k_SeeOther($this->url($id . '/state'));
            } else {
                return new k_SeeOther($this->url('../'));
            }

        }
        return $this->render();
    }

    function getDebtor()
    {
        return $this->context->getModel();
    }

    function getModel()
    {
        if (is_object($this->payment)) {
            return $this->payment;
        }

        $gateway = new Intraface_modules_invoice_PaymentGateway($this->getKernel());
        return $this->payment = $gateway->findById($this->name());
    }

    function getPayment()
    {
        return $this->getModel();
    }

    function getType()
    {
        return $this->context->getType();
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

}