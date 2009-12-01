<?php
class Intraface_modules_accounting_Controller_State_Payment extends k_Component
{
    protected $year;

    function getModel()
    {
        return $object = $this->context->context->context->getObject();
    }

    function map()
    {
        return 'Intraface_modules_accounting_Controller_State_SelectYear';
    }

    function renderHtml()
    {
        $procurement_module = $this->getKernel()->module('procurement');
        $accounting_module = $this->getKernel()->useModule('accounting');
        $translation = $this->getKernel()->getTranslation('procurement');

        $year = new Year($this->getKernel());
        $voucher = new Voucher($year);
        if (!$this->getYear()->readyForState($this->getModel()->get('payment_date'))) {
            return new k_SeeOther($this->url('selectyear'));
        }
        $procurement = new Procurement($this->getKernel(), intval($this->context->name()));
        $value = $procurement->get();
        $procurement->loadItem();
        $items = $procurement->item->getList();
        $i = 0;
        $items_amount = 0;

        if (count($items) > 0) {
            /**
             * implement to a line for each item
             */
        }

        if ($procurement->get('price_items') - $items_amount > 0) {
            $value['debet_account'][$i++] = array('text' => '', 'amount' => number_format($procurement->get('price_items') - $items_amount, 2, ',', '.'));
        }

        if ($procurement->get('price_shipment_etc') > 0) {
            $value['debet_account'][$i++] = array('text' => __('shipment etc'), 'amount' => $procurement->get('dk_price_shipment_etc'));
        }
        $smarty = new k_Template(dirname(__FILE__) . '/../templates/state/payment.tpl.php');
        return $smarty->render($this, array('payment' => $payment, 'object' => $object, 'year' => $year));

    }

    function postForm()
    {
        $procurement_module = $this->getKernel()->module('procurement');
        $accounting_module = $this->getKernel()->useModule('accounting');
        $translation = $this->getKernel()->getTranslation('procurement');

        $year = new Year($this->getKernel());
        $voucher = new Voucher($year);

        $procurement = new Procurement($this->getKernel(), intval($this->context->name()));

        if (isset($_POST['state'])) {

            if ($procurement->checkStateDebetAccounts($year, $_POST['debet_account'])) {
                if ($procurement->state($year, $_POST['voucher_number'], $_POST['voucher_date'], $_POST['debet_account'], (int)$_POST['credit_account_number'], $translation)) {
                    header('location: view.php?id='.$procurement->get('id'));
                    exit;
                }
                $procurement->error->set('Kunne ikke bogføre posten');
            }
        }

        $value = $_POST;

        if (isset($_POST['add_line'])) {
            array_push($value['debet_account'], array('text' => '', 'amount' => '0,00'));
        }

        if (isset($_POST['remove_line'])) {
            foreach ($_POST['remove_line'] AS $key => $void) {
                array_splice($value['debet_account'], $key, 1);
            }
        }

        return $this->render();

    }
}