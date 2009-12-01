<?php
class Intraface_modules_accounting_Controller_State_Payment extends k_Component
{
    protected $year;

    function getModel()
    {
        return $object = $this->context->context->context->getModel();
    }

    function map($name)
    {
        return 'Intraface_modules_accounting_Controller_State_SelectYear';
    }

    function renderHtml()
    {
        $accounting_module = $this->context->getKernel()->useModule('accounting');
        $this->context->getKernel()->useModule('invoice');
        $translation = $this->context->getKernel()->getTranslation('debtor');
        $year = new Year($this->context->getKernel());
        $voucher = new Voucher($year);
        $object = $this->context->context->context->getModel();
        $payment = new Payment($object, $this->context->name());
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
        $voucher = new Voucher($year);

        $object = $this->context->context->context->getModel();

        $payment = new Payment($object, intval($this->context->name()));

        $this->context->getKernel()->getSetting()->set('intranet', 'payment.state.'.$payment->get('type').'.account', intval($_POST['state_account_id']));

        if ($payment->error->isError()) {
            // nothing, we continue
        } elseif (!$payment->state($year, $_POST['voucher_number'], $_POST['date_state'], $_POST['state_account_id'], $translation)) {
            $payment->error->set('Could not state');
        } else {
            return new k_SeeOther($this->url('../'));
        }

        return $this->render();

    }

    function getYear()
    {
        return new Year($this->getKernel());
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }
}