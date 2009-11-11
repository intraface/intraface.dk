<?php
class Intraface_modules_accounting_Controller_State_Depreciation extends k_Component
{
    function getType()
    {
        return $this->context->context->context->getType();
    }

    function renderHtml()
    {
        $debtor_module = $this->context->getKernel()->module('accounting');

        $smarty = new k_Template(dirname(__FILE__) . '/../templates/state/depreciation.tpl.php');
        return $smarty->render($this, array('voucher' => $voucher, 'year' => $this->getYear(), 'depreciation' => $this->context->getDepreciation(), 'object' => $this->getObject(), 'year' => $year));

    }

    function getObject()
    {
        return $this->context->getObject();
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

    function postForm()
    {
        $debtor_module = $this->context->getKernel()->module('accounting');

        $debtor_module = $this->context->getKernel()->module('debtor');
        $accounting_module = $this->context->getKernel()->useModule('accounting');
        $this->context->getKernel()->useModule('invoice');

        $year = new Year($this->context->getKernel());
        $voucher = new Voucher($year);

        if (!empty($_POST)) {

            if (empty($_POST['for'])) {
                trigger_error('you need to provide what the depreciation is for', E_USER_ERROR);
                exit;
            }

            switch($_POST['for']) {
                case 'invoice':
                    $object = new Invoice($this->context->getKernel(), intval($_POST["id"]));
                    $for = 'invoice';
                break;
                case 'reminder':
                    $object = new Reminder($this->context->getKernel(), intval($_POST['id']));
                    $for = 'reminder';
                break;
                default:
                    trigger_error('Invalid for', E_USER_ERROR);
                    exit;
            }

            if ($object->get('id') == 0) {
                trigger_error('Invalid '.$for.' #'. $_POST["id"], E_USER_ERROR);
                exit;
            }
            $depreciation = new Depreciation($object, intval($_POST['depreciation_id']));
            if ($depreciation->get('id') == 0) {
                trigger_error('Invalid depreciation #'. $_POST["depreciation_id"], E_USER_ERROR);
                exit;
            }

            $this->context->getKernel()->setting->set('intranet', 'depreciation.state.account', intval($_POST['state_account_id']));

            if ($depreciation->error->isError()) {
                // nothing, we continue
            } elseif (!$depreciation->state($year, $_POST['voucher_number'], $_POST['date_state'], $_POST['state_account_id'], $translation)) {
                $depreciation->error->set('Kunne ikke bogf�re posten');
            } else {

                if ($for == 'invoice') {
                    header('Location: view.php?id='.$object->get('id'));
                    exit;
                } elseif ($for == 'reminder') {
                    header('Location: reminder.php?id='.$object->get('id'));
                    exit;
                }
            }
        }
    }

    function t($phrase)
    {
        return $phrase;
    }
}
