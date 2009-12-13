<?php
class Intraface_modules_accounting_Controller_State_Procurement extends k_Component
{
    protected $year;
    protected $value = array();

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getModel()
    {
        return $object = $this->context->getModel();
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
        if (!$this->getYear()->readyForState($this->getModel()->get('this_date'))) {
            return new k_SeeOther($this->url('selectyear'));
        }
        $procurement = $this->getProcurement();
        //$this->value = $procurement->get();
        $items = $procurement->getItems();
        $i = 0;
        $items_amount = 0;

        if (count($items) > 0) {
            // implement to a line for each item
        }
        /*
         if ($procurement->get('price_items') - $items_amount > 0) {
         $this->value['debet_account'][$i++] = array('text' => '', 'amount' => number_format($procurement->get('price_items') - $items_amount, 2, ',', '.'));
         }

         if ($procurement->get('price_shipment_etc') > 0) {
         $this->value['debet_account'][$i++] = array('text' => $this->t('shipment etc'), 'amount' => $procurement->get('dk_price_shipment_etc'));
         }
         */
        $smarty = new k_Template(dirname(__FILE__) . '/../templates/state/procurement.tpl.php');

        $data = array(
        	'procurement' => $procurement,
        	'year' => $year,
        	'voucher' => $voucher,
        	'items' => $items,
        	'value' => $this->getValues());

        return $smarty->render($this, $data);

    }

    function getYear()
    {
        return $year = new Year($this->getKernel());
    }

    function getProcurement()
    {
        return $this->context->getProcurement();
    }

    function getValues()
    {
        if ($this->body()) {
            $this->value = $this->body();
            if (isset($_POST['add_line'])) {
                array_push($this->value['debet_account'], array('text' => '', 'amount' => '0,00'));
                return $this->value;
            } elseif (isset($_POST['remove_line'])) {
                foreach ($_POST['remove_line'] AS $key => $void) {
                    array_splice($this->value['debet_account'], $key, 1);
                }
                return $this->value;
            }

        } else {
            $i = 0;
            $procurement = $this->getProcurement();
            $this->value = $procurement->get();
            if ($procurement->get('price_items') - $items_amount > 0) {
                $this->value['debet_account'][$i++] = array('text' => '', 'amount' => number_format($procurement->get('price_items') - $items_amount, 2, ',', '.'));
            }

            if ($procurement->get('price_shipment_etc') > 0) {
                $this->value['debet_account'][$i++] = array('text' => $this->t('shipment etc'), 'amount' => $procurement->get('dk_price_shipment_etc'));
            }
        }

        return $this->value;
    }

    function postForm()
    {
        $procurement_module = $this->getKernel()->module('procurement');
        $accounting_module = $this->getKernel()->useModule('accounting');

        $year = new Year($this->getKernel());
        $voucher = new Voucher($year);

        $procurement = $this->getProcurement();

        if (isset($_POST['state'])) {
            if ($procurement->checkStateDebetAccounts($year, $_POST['debet_account'])) {
                if ($procurement->state($year, $_POST['voucher_number'], $_POST['voucher_date'], $_POST['debet_account'], (int)$_POST['credit_account_number'], $this->getKernel()->getTranslation('procurement'))) {
                    return new k_SeeOther($this->url('../'));
                }
                $procurement->error->set('Kunne ikke bogføre posten');
            }
        }

        return $this->render();
    }
}