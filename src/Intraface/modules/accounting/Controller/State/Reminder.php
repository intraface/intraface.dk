<?php
class Intraface_modules_accounting_Controller_State_Reminder extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function map()
    {
        return 'Intraface_modules_accounting_Controller_State_SelectYear';
    }

    function renderHtml()
    {
        $debtor_module = $this->context->getKernel()->module('debtor');
        $accounting_module = $this->context->getKernel()->useModule('invoice');
        $accounting_module = $this->context->getKernel()->useModule('accounting');
        $product_module = $this->context->getKernel()->useModule('product');

        $year = $this->getYear();
        $voucher = new Voucher($year);

        $reminder = $this->getReminder();
        $value = $reminder->get();

        if (!$this->getYear()->readyForState($this->getModel()->get('this_date'))) {
            return new k_SeeOther($this->url('selectyear'));
        }

        $smarty = $this->template->create(dirname(__FILE__) . '/../templates/state/reminder');
        return $smarty->render($this, array('voucher' => $voucher, 'year' => $this->getYear(), 'reminder' => $reminder, 'value' => $value, 'year' => $year));
    }

    function postForm()
    {
        $debtor_module = $this->context->getKernel()->module('debtor');
        $accounting_module = $this->context->getKernel()->useModule('invoice');
        $accounting_module = $this->context->getKernel()->useModule('accounting');
        $product_module = $this->context->getKernel()->useModule('product');

        $year = $this->getYear();

        if (!empty($_POST)) {
            $reminder = $this->getReminder();

            if ($reminder->error->isError()) {
                $reminder->loadItem();
            } elseif (!$reminder->state($year, $_POST['voucher_number'], $_POST['date_state'], $_POST['state_account_id'], $this->context->getKernel()->getTranslation('debtor'))) {
                $reminder->error->set('unable to state the reminder');
                $reminder->loadItem();
            } else {
                return new k_SeeOther($this->url('../'));
            }
        }
        return $this->render();
    }

    function getYear()
    {
        return $year = new Year($this->context->getKernel());
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getReminder()
    {
        return $this->context->getReminder();
    }

    function getModel()
    {
        return $this->context->getReminder();
    }
}
