<?php
class Intraface_modules_accounting_Controller_State_Payment extends k_Component
{
    protected $registry;
    protected $year;

    function renderHtml()
    {
        $accounting_module = $this->context->getKernel()->useModule('accounting');
        $this->context->getKernel()->useModule('invoice');
        $translation = $this->context->getKernel()->getTranslation('debtor');
        $year = new Year($this->context->getKernel());
        $voucher = new Voucher($year);
        $object = $this->context->context->context->getObject();
        $payment = new Payment($object, $this->context->name());
        $smarty = new k_Template(dirname(__FILE__) . '/../templates/state/payment.tpl.php');
        return $smarty->render($this, array('payment' => $payment, 'object' => $object, 'year' => $year));

    }

    function t($phrase)
    {
        return $phrase;
    }

    function postForm()
    {
        $accounting_module = $this->context->getKernel()->useModule('accounting');
        $this->context->getKernel()->useModule('invoice');
        $translation = $this->context->getKernel()->getTranslation('debtor');

        $year = new Year($this->context->getKernel());
        $voucher = new Voucher($year);

      $object = $this->context->context->context->getObject();


          $payment = new Payment($object, intval($this->context->name()));


    $this->context->getKernel()->setting->set('intranet', 'payment.state.'.$payment->get('type').'.account', intval($_POST['state_account_id']));

    if ($payment->error->isError()) {
        // nothing, we continue
    } elseif (!$payment->state($year, $_POST['voucher_number'], $_POST['date_state'], $_POST['state_account_id'], $translation)) {
        $payment->error->set('Kunne ikke bogf�re posten');
    } else {

        if ($for == 'invoice') {
            header('Location: view.php?id='.$object->get('id'));
            exit;
        } elseif ($for == 'reminder') {
            header('Location: reminder.php?id='.$object->get('id'));
            exit;
        }
    }

    return $this->render();

    }
}