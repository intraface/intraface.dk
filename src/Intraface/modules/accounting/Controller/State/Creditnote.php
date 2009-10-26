<?php
class Intraface_modules_accounting_Controller_State_Creditnote extends k_Component
{
    protected $registry;

    function __construct(k_Registry $registry)
    {
        $this->registry = $registry;
    }

    function getDebtor()
    {
        return $this->context->getDebtor();
    }

    function renderHtml()
    {
        $debtor_module = $kernel->module('debtor');
        $accounting_module = $kernel->useModule('accounting');
        $product_module = $kernel->useModule('product');
        $translation = $kernel->getTranslation('debtor');

        $debtor = $this->getDebtor();

        if ($debtor->get('type') != 'credit_note') {
            trigger_error('You can only state credit notes from this page', E_USER_ERROR);
            exit;
        }

        $debtor->loadItem();

        $items = $debtor->item->getList();
        $value = $debtor->get();

        $smarty = new k_Template(dirname(__FILE__) . '/../templates/state/creditnote.tpl.php');
        return $smarty->render($this);

    }

    function postForm()
    {
        $debtor_module = $kernel->module('debtor');
        $accounting_module = $kernel->useModule('accounting');
        $product_module = $kernel->useModule('product');
        $translation = $kernel->getTranslation('debtor');

        $year = new Year($kernel);
        $voucher = new Voucher($year);

        if (!empty($_POST)) {

            $debtor = $this->getDebtor();
            if ($debtor->get('type') != 'credit_note') {
                trigger_error('You can only state credit notes from this page', E_USER_ERROR);
                exit;
            }

            foreach ($_POST['state_account_id'] as $product_id => $state_account_id) {
                if (empty($state_account_id)) {
                    $debtor->error->set('Mindst et produkt ved ikke hvor det skal bogf�res.');
                    continue;
                }

                $product = new Product($kernel, $product_id);
                $product->getDetails()->setStateAccountId($state_account_id);
            }

            if ($debtor->error->isError()) {
                $debtor->loadItem();
            } elseif (!$debtor->state($year, $_POST['voucher_number'], $_POST['date_state'], $translation)) {
                $debtor->error->set('Kunne ikke bogf�re posten');
                $debtor->loadItem();
            } else {
                header('Location: view.php?id='.$debtor->get('id'));
                exit;
            }
        }

    }
}