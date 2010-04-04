<?php
class Intraface_modules_payment_Controller_Index extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function map($name)
    {
        if (is_numeric($name)) {
            return 'Intraface_modules_payment_Controller_Show';
        }
    }

    function renderHtml()
    {
        $this->getKernel()->useModule('invoice');

        $gateway = new Intraface_modules_invoice_PaymentGateway($this->getKernel());
        $payments = $gateway->findAll();

        $data = array('payments' => $payments);

        $smarty = $this->template->create(dirname(__FILE__) . '/templates/index');
        return $smarty->render($this, $data);
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }
}