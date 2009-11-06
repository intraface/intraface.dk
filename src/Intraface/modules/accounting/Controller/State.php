<?php
class Intraface_modules_accounting_Controller_State extends k_Component
{
    protected $registry;

    protected function map($name)
    {
    }

    function __construct(k_Registry $registry)
    {
        $this->registry = $registry;
    }

    function renderHtml()
    {
        $module = $this->getKernel()->module('accounting');
        $translation = $this->getKernel()->getTranslation('accounting');

        if (!empty($_GET['message']) AND in_array($_GET['message'], array('hide'))) {
            $this->getKernel()->setting->set('user', 'accounting.state.message', 'hide');
        } elseif (!empty($_GET['message2']) AND in_array($_GET['message2'], array('hide'))) {
            $this->getKernel()->setting->set('user', 'accounting.state.message2', 'hide');
        }

        $tpl = new k_Template(dirname(__FILE__) . '/templates/state.tpl.php');
        return $tpl->render($this);
    }

    function getPosts()
    {
        return $this->getPost()->getList('draft');
    }

    function getAccounts()
    {
        return $this->getYear()->getBalanceAccounts();
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getYear()
    {
        $module = $this->getKernel()->module('accounting');
        $translation = $this->getKernel()->getTranslation('accounting');

        $year = $this->context->getYear();
        $year->checkYear();
        return $year;
    }

    function getYearGateway()
    {
        return new Intraface_modules_accounting_YearGateway($this->getKernel());
    }

    function postForm()
    {
        $module = $this->getKernel()->module('accounting');
        $translation = $this->getKernel()->getTranslation('accounting');

        $voucher = new Voucher($this->getYear());
        // denne funktion v�lger automatisk alle poster i kassekladden
        if (!$voucher->stateDraft()) {
            // $post->error->set('Posterne kunne ikke bogf�res');
        }

        return new k_SeeOther($this->url());
    }

    function getVoucher()
    {
        require_once dirname(__FILE__) . '/../Voucher.php';
        return new Voucher($this->getYear());
    }
    /*
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
    */

    function getAccount()
    {
    	return new Account($this->getYear());
    }

    function getPost()
    {
    	return new Post($this->getVoucher());
    }

}

