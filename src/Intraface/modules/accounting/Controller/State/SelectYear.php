<?php
class Intraface_modules_accounting_Controller_State_SelectYear extends Intraface_modules_accounting_Controller_Year_Index
{
    protected $year;

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getModel()
    {
        return $this->context->getModel();
    }

    function getYear()
    {
        $accounting_module = $this->getKernel()->useModule('accounting');
        if (is_object($this->year)) {
            return $this->year;
        }

        return $this->year = new Year($this->getKernel());
    }

    function getVoucher()
    {
        return $voucher = new Voucher($this->getYear());
    }

    function getModule()
    {
        return $accounting_module = $this->getKernel()->useModule('accounting');
    }

    function renderHtml()
    {
        $debtor_module = $this->getKernel()->module('debtor');
        $accounting_module = $this->getKernel()->useModule('accounting');
        $product_module = $this->getKernel()->useModule('product');
        $translation = $this->getKernel()->getTranslation('debtor');

        if (!$this->getYear()->readyForState($this->getModel()->get('this_date'))) {
            $smarty = new k_Template(dirname(__FILE__) . '/../templates/state/year-not-ready.tpl.php');
            return $smarty->render($this);
        }

        return new k_SeeOther($this->url('../'));
    }

    function putForm()
    {
        $accounting_module = $this->getKernel()->useModule('accounting');
        $year = new Year($this->getKernel(), $this->body('year_id'));
        if ($year->setYear()) {
            return new k_SeeOther($this->url('../'));
        }
        throw new Exception('Could not set the year');
    }

    function getYears()
    {
        return $this->getYear()->getList();
    }
}