<?php
class Intraface_modules_accounting_Controller_State_SelectYear extends Intraface_modules_accounting_Controller_Year_Index
{
    protected $registry;
    protected $year;

    function __construct(k_Registry $registry)
    {
        $this->registry = $registry;
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getDebtor()
    {
        return $this->context->getDebtor();
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

        $year = new Year($this->getKernel());
        $voucher = new Voucher($year);

        $debtor = $this->getDebtor();
        if ($debtor->get('type') != 'invoice') {
            throw new Exception('You can only state invoice from this page');
        }
        $debtor->loadItem();
        $items = $debtor->item->getList();

        if (!$this->getYear()->readyForState($this->getDebtor()->get('this_date'))) {
            $smarty = new k_Template(dirname(__FILE__) . '/../templates/state/year-not-ready.tpl.php');
            return $smarty->render($this);
        }

        return new k_SeeOther($this->url('../'));
    }

    function getYears()
    {
        return $this->getYear()->getList();
    }

    function t($phrase)
    {
        return $phrase;
    }
}