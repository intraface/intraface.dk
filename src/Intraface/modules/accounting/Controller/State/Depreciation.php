<?php
class Intraface_modules_accounting_Controller_State_Depreciation extends k_Component
{
    function map()
    {
        return 'Intraface_modules_accounting_Controller_State_SelectYear';
    }

    function renderHtml()
    {
        $accounting_module = $this->context->getKernel()->module('accounting');

        if (!$this->getYear()->readyForState($this->getDepreciation()->get('payment_date'))) {
            return new k_SeeOther($this->url('selectyear'));
        }
        $smarty = new k_Template(dirname(__FILE__) . '/../templates/state/depreciation.tpl.php');
        return $smarty->render($this, array('accounting_module' => $accounting_module, 'voucher' => $voucher, 'year' => $this->getYear(), 'depreciation' => $this->context->getDepreciation(), 'object' => $this->getModel(), 'year' => $year));

    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getDepreciation()
    {
        return $this->context->getDepreciation();
    }

    function postForm()
    {
        $debtor_module = $this->context->getKernel()->module('accounting');

        $debtor_module = $this->context->getKernel()->module('debtor');
        $accounting_module = $this->context->getKernel()->useModule('accounting');
        $this->context->getKernel()->useModule('invoice');

        $year = new Year($this->context->getKernel());
        $voucher = new Voucher($year);

        if (!$this->getYear()->readyForState($this->getDepreciation()->get('payment_date'))) {
            return new k_SeeOther($this->url('selectyear'));
        }

        $depreciation = $this->context->getDepreciation();


            $this->context->getKernel()->getSetting()->set('intranet', 'depreciation.state.account', intval($_POST['state_account_id']));

            if ($depreciation->error->isError()) {
                // nothing, we continue
            } elseif (!$depreciation->state($year, $_POST['voucher_number'], $_POST['date_state'], $_POST['state_account_id'], $translation)) {
                $depreciation->error->set('Kunne ikke bogf�re posten');
            } else {
                return new k_SeeOther($this->url('../../'));
            }
            return $this->render();
    }

    function getType()
    {
        return $this->context->context->context->getType();
    }


    function getModel()
    {
        return $this->context->getModel();
    }

    function getYear()
    {
        $year = new Year($this->context->getKernel());
        $year->loadActiveYear();
        return $year;
    }

    function getVoucher()
    {
        return $voucher = new Voucher($this->getYear());
    }

    function t($phrase)
    {
        return $phrase;
    }
}
