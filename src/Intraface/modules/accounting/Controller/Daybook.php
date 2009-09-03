<?php
class Intraface_modules_accounting_Controller_Daybook extends k_Component
{
    protected $registry;

    protected function map($name)
    {
        if ($name == 'year') {
            return 'Intraface_modules_accounting_Controller_Year_Index';
        }
    }

    function __construct(WireFactory $registry)
    {
        $this->registry = $registry;
    }

    function renderHtml()
    {
        if (!empty($_GET['message']) AND in_array($_GET['message'], array('hide'))) {
            $this->getKernel()->setting->set('user', 'accounting.daybook.message', 'hide');
        } elseif (!empty($_GET['view']) AND in_array($_GET['view'], array('income', 'expenses', 'classic', 'debtor'))) {
            $this->getKernel()->setting->set('user', 'accounting.daybook_view', $_GET['view']);
        } elseif (!empty($_GET['quickhelp']) AND in_array($_GET['quickhelp'], array('true', 'false'))) {
            $this->getKernel()->setting->set('user', 'accounting.daybook_cheatsheet', $_GET['quickhelp']);
            if (isAjax()) {
                echo '1';
                exit;
            }
        }

        $tpl = new k_Template(dirname(__FILE__) . '/templates/daybook.tpl.php');
        return $tpl->render($this);
    }

    function getKernel()
    {
        $registry = $this->registry->create();
        return $registry->get('kernel');
    }

    function getYear()
    {
        $year = $this->context->getModel();
        $year->checkYear();
        return $year;
    }

    function getYearGateway()
    {
        return new Intraface_modules_accounting_YearGateway($this->getKernel());
    }

    function POST()
    {
        $this->getVoucher();
        // tjek om debet og credit account findes
        $voucher = Voucher::factory($this->getYear(), $_POST['voucher_number']);
        if ($id = $voucher->saveInDaybook($_POST)) {
            header('Location: daybook.php?from_post_id='.$id);
            exit;
        } else {
            $values = $_POST;
        }
    }

    function getVoucher()
    {
        require_once dirname(__FILE__) . '/../Voucher.php';
        return new Voucher($this->getYear());
    }

    function getValues()
    {
        $values['voucher_number'] = $this->getVoucher()->getMaxNumber() + 1;
        $values['date'] = date('d-m-Y');
        $values['debet_account_number'] = '';
        $values['credit_account_number'] = '';
        $values['amount'] = '';
        $values['text'] = '';
        $values['reference'] = '';
        $values['id'] = '';

    	return $values;
    }

    function getAccount()
    {
    	return new Account($this->getYear());
    }

    function getPost()
    {
    	return new Post($this->getVoucher());
    }

}