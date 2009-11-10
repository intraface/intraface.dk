<?php
class Intraface_modules_accounting_Controller_Settings extends k_Component
{
    protected function map($name)
    {
        if ($name == 'year') {
            return 'Intraface_modules_accounting_Controller_Year_Index';
        }
    }

    function renderHtml()
    {
        $smarty = new k_Template(dirname(__FILE__) . '/templates/settings.tpl.php');
        return $smarty->render($this);
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getYear()
    {
        $year = $this->context->getYear();
        if (!$year->isYearSet()) {
            return new k_SeeOther($this->url('year'));
        }
        return $year;
    }

    function getYearGateway()
    {
        return new Intraface_modules_accounting_YearGateway($this->getKernel());
    }

    function POST()
    {
        $this->getYear()->setSettings($_POST);
        return k_SeeOther($this->url());
    }

    function getVoucher()
    {
        $voucher = new Voucher($this->getYear());
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