<?php
class Intraface_modules_payment_Controller_Show extends k_Component
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

    function getKernel()
    {
        return $this->context->getKernel();
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

    function renderHtml()
    {
        return new k_SeeOther($this->url('state'));
    }
}
