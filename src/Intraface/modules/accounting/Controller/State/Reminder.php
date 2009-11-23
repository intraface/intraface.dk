<?php
class Intraface_modules_accounting_Controller_State_Reminder extends k_Component
{
    function map()
    {
        return 'Intraface_modules_accounting_Controller_State_SelectYear';
    }

    function getModel()
    {
        return $this->context->getReminder();
    }

    function renderHtml()
    {
        $debtor_module = $this->context->getKernel()->module('debtor');
        $accounting_module = $this->context->getKernel()->useModule('invoice');
        $accounting_module = $this->context->getKernel()->useModule('accounting');
        $product_module = $this->context->getKernel()->useModule('product');
        $translation = $this->context->getKernel()->getTranslation('debtor');

        $year = new Year($this->context->getKernel());
        $voucher = new Voucher($year);

        $reminder = new Reminder($this->context->getKernel(), intval($this->context->name()));
        $value = $reminder->get();

        if (!$this->getYear()->readyForState($this->getModel()->get('this_date'))) {
            return new k_SeeOther($this->url('selectyear'));
        }


        $smarty = new k_Template(dirname(__FILE__) . '/../templates/state/reminder.tpl.php');
        return $smarty->render($this, array('voucher' => $voucher, 'year' => $this->getYear(), 'reminder' => $reminder, 'value' => $value, 'year' => $year));
    }

    function getYear()
    {
        return $year = new Year($this->context->getKernel());
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function postForm()
    {
        $debtor_module = $this->context->getKernel()->module('debtor');
        $accounting_module = $this->context->getKernel()->useModule('invoice');
        $accounting_module = $this->context->getKernel()->useModule('accounting');
        $product_module = $this->context->getKernel()->useModule('product');
        $translation = $this->context->getKernel()->getTranslation('debtor');

        $year = new Year($this->context->getKernel());
        $voucher = new Voucher($year);

        if (!empty($_POST)) {
            $reminder = new Reminder($this->context->getKernel(), intval($_POST["id"]));

            if ($reminder->error->isError()) {
                $reminder->loadItem();
            } elseif (!$reminder->state($year, $_POST['voucher_number'], $_POST['date_state'], $_POST['state_account_id'], $translation)) {
                $reminder->error->set('unable to state the reminder');
                $reminder->loadItem();
            } else {
                return new k_SeeOther($this->url('../'));
            }
        }
        return $this->render();
    }
}