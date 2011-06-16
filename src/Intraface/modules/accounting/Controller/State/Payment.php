<?php
class Intraface_modules_accounting_Controller_State_Payment extends k_Component
{
    protected $year;
    protected $voucher;
    protected $payment;
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function map($name)
    {
        return 'Intraface_modules_accounting_Controller_State_SelectYear';
    }

    function renderHtml()
    {
        $this->document->setTitle('State payment');

        $accounting_module = $this->context->getKernel()->useModule('accounting');
        $this->context->getKernel()->useModule('invoice');
        $voucher = $this->getVoucher();
        //$object = $this->context->getDebtor();
        $payment = $this->getModel();

        if (!$this->getYear()->readyForState($this->getModel()->get('this_date'))) {
            return new k_SeeOther($this->url('selectyear'));
        }

        $data = array(
        	'kernel' => $this->getKernel(),
        	'voucher' => $voucher,
        	'payment' => $payment,
        	//'object' => $object,
        	'year' => $this->getYear(),
            'accounting_module' => $accounting_module);

        $smarty = $this->template->create(dirname(__FILE__) . '/../templates/state/payment');
        return $smarty->render($this, $data);
    }

    function postForm()
    {
        $accounting_module = $this->context->getKernel()->useModule('accounting');
        $this->context->getKernel()->useModule('invoice');
        $voucher = $this->getVoucher();

        $payment = $this->getModel();

        $this->context->getKernel()->getSetting()->set('intranet', 'payment.state.'.$payment->get('type').'.account', intval($this->body('state_account_id')));

        if ($payment->error->isError()) {
            // nothing, we continue
        } elseif (!$payment->state($this->getYear(), $this->body('voucher_number'), $this->body('date_state'), $this->body('state_account_id'), $this->context->getKernel()->getTranslation('debtor'))) {
            $payment->error->set('Could not state');
        } else {
            return new k_SeeOther($this->url('../../', array('use_stored' => 'true')));
        }

        return $this->render();

    }

    function getModel()
    {
        return $this->context->getModel();
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

    function getKernel()
    {
        return $this->context->getKernel();
    }
}