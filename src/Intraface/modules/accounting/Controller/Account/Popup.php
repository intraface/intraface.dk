<?php
class Intraface_modules_accounting_Controller_Account_Popup extends k_Component
{
    protected $registry;

    function __construct(WireFactory $registry)
    {
        $this->registry = $registry;
    }

    function getAccountingModule()
    {
        $accounting_module = $this->getKernel()->module('accounting');
        return $accounting_module;
    }

    function getAccounts()
    {
        $accounting_module = $this->getKernel()->module('accounting');

        $account = new Account(new Year($this->getKernel()));
        return $account->getList();
    }

    function renderHtml()
    {
        $smarty = new k_Template(dirname(__FILE__) . '/../templates/account/popup.tpl.php');
        return $smarty->render($this);
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getYear($id = 0)
    {
        $module = $this->getKernel()->module('accounting');
        $translation = $this->getKernel()->getTranslation('accounting');

        return new Year($this->getKernel(), $id);
    }
}