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
        
        if ($this->query("text") != "") {
            $gateway->getDBQuery()->setFilter("text", $this->query("text"));
        }
        if ($this->query("from_date") != "") {
            $gateway->getDBQuery()->setFilter("from_date", $this->query("from_date"));
        }
        if ($this->query("to_date") != "") {
            $gateway->getDBQuery()->setFilter("to_date", $this->query("to_date"));
        }
        if ($this->query("status")) {
            $gateway->getDBQuery()->setFilter("status", $this->query("status"));
        }
        if ($this->query('not_stated')) {
            $gateway->getDBQuery()->setFilter("not_stated", "1");
        } else {
            $gateway->getDBQuery()->setFilter("status", "-2");
        }
    
        $gateway->getDBQuery()->usePaging("paging", $this->getKernel()->setting->get('user', 'rows_pr_page'));
        $gateway->getDBQuery()->storeResult("use_stored", "payment", "toplevel");
        $gateway->getDBQuery()->setUri($this->url(null, array('use_stored' => 'true')));        
        
        $payments = $gateway->findAll();

        $data = array('payments' => $payments, 'gateway' => $gateway);

        $smarty = $this->template->create(dirname(__FILE__) . '/templates/index');
        return $smarty->render($this, $data);
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }
}
