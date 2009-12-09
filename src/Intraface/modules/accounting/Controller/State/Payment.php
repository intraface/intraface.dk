<?php
class Intraface_modules_accounting_Controller_State_Payment extends k_Component
{
    protected $year;
    protected $voucher;
    protected $payment;

    function getModel()
    {
        return $object = $this->context->context->context->getModel();
    }

    function getVoucher()
    {
        if (is_object($this->voucher)) {
            return $this->voucher;
        }

        return $this->voucher = new Voucher($this->getYear());
    }

    function getYear()
    {
        return new Year($this->getKernel());
    }

    function map($name)
    {
        return 'Intraface_modules_accounting_Controller_State_SelectYear';
    }

    function getPayment()
    {
        if (is_object($this->payment)) return $this->payment;
        return $this->payment = new Payment($this->getModel(), $this->context->name());
    }

    function renderHtml()
    {
        $accounting_module = $this->context->getKernel()->useModule('accounting');
        $this->context->getKernel()->useModule('invoice');
        $translation = $this->context->getKernel()->getTranslation('debtor');
        $year = new Year($this->context->getKernel());
        $voucher = $this->getVoucher();
        $object = $this->context->context->context->getModel();
        $payment = $this->getPayment();
        if (!$this->getYear()->readyForState($this->getModel()->get('this_date'))) {
            return new k_SeeOther($this->url('selectyear'));
        }

        $smarty = new k_Template(dirname(__FILE__) . '/../templates/state/payment.tpl.php');
        return $smarty->render($this, array('kernel' => $this->getKernel(), 'voucher' => $voucher, 'payment' => $payment, 'object' => $object, 'year' => $year));

    }

    function postForm()
    {
        $accounting_module = $this->context->getKernel()->useModule('accounting');
        $this->context->getKernel()->useModule('invoice');
        $translation = $this->context->getKernel()->getTranslation('debtor');
        $year = new Year($this->context->getKernel());
        $voucher = $this->getVoucher();

        $object = $this->context->context->context->getModel();

        $payment = $this->getPayment();

        $this->context->getKernel()->getSetting()->set('intranet', 'payment.state.'.$payment->get('type').'.account', intval($_POST['state_account_id']));

        if ($payment->error->isError()) {
            // nothing, we continue
        } elseif (!$payment->state($year, $_POST['voucher_number'], $_POST['date_state'], $_POST['state_account_id'], $translation)) {
            $payment->error->set('Could not state');
        } else {
            return new k_SeeOther($this->url('../../../'));
        }

        return $this->render();

    }

    function getKernel()
    {
        return $this->context->getKernel();
    }
}