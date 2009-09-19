<?php
class Intraface_modules_accounting_Controller_Account_Index extends k_Component
{
    protected $registry;

    protected function map($name)
    {
        if ($name == 'create') {
            return 'Intraface_modules_accounting_Controller_Account_Edit';
        } elseif (is_numeric($name)) {
        	return 'Intraface_modules_accounting_Controller_Account_Show';
        } elseif ($name == 'popup') {
        	return 'Intraface_modules_accounting_Controller_Account_Popup';
        }
    }

    function __construct(WireFactory $registry)
    {
        $this->registry = $registry;
    }

    function GET()
    {
        $year = $this->getYear();
        $year->checkYear();

        if (!empty($_GET['action']) AND $_GET['action'] == 'delete' AND is_numeric($_GET['id'])) {
            $account = new Account($year, $_GET['id']);
            $account->delete();
        } else {
            $account = new Account($year);
            $values['from_date'] = $year->get('from_date_dk');
            $values['to_date'] = $year->get('to_date_dk');
        }

        //$accounts = $account->getSaldoList($values['from_date'], $values['to_date']);
        $accounts = $account->getList('stated', true);

        parent::GET();
    }

    function renderHtml()
    {
        $smarty = new k_Template(dirname(__FILE__) . '/../templates/account/index.tpl.php');
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

    function getAccountsGateway()
    {
        $gateway = $this->context->getYearGateway();
        return $gateway;
    }

}