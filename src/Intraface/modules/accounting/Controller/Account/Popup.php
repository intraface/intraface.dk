<?php
class Intraface_modules_accounting_Controller_Account_Popup extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function getAccountingModule()
    {
        $accounting_module = $this->getKernel()->module('accounting');
        return $accounting_module;
    }

    function getAccounts()
    {
        $gateway = new Intraface_modules_accounting_AccountGateway($this->getYear());
    	return $gateway->getAll();
    }

    function renderHtml()
    {
        $this->document->setTitle('Accounts');

        $this->document->addStyle($this->url('accounting/daybook_list_account.js'));

        $smarty = $this->template->create(dirname(__FILE__) . '/../templates/account/popup');

        $response = new k_HttpResponse(200, $smarty->render($this), true);
        $response->setContentType('text/html');
        return $response;
    }

    function getDocument()
    {
        return $this->document;
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getYear()
    {
        return $this->context->getYear();
    }
}