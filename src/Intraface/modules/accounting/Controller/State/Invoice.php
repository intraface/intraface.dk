<?php
class Intraface_modules_accounting_Controller_State_Invoice extends k_Component
{
    protected $registry;
    protected $year;

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function __construct(k_Registry $registry)
    {
        $this->registry = $registry;
    }

    function getDebtor()
    {
        return $this->context->getDebtor();
    }

    function getYear()
    {
        if (is_object($this->year)) {
            return $this->year;
        }

        return $this->year = new Year($this->getKernel());
    }

    function getVoucher()
    {
        return $voucher = new Voucher($this->getYear());

    }

    function getModule()
    {
        return $accounting_module = $this->getKernel()->useModule('accounting');
    }

    function renderHtml()
    {
        $debtor_module = $this->getKernel()->module('debtor');
        $accounting_module = $this->getKernel()->useModule('accounting');
        $product_module = $this->getKernel()->useModule('product');
        $translation = $this->getKernel()->getTranslation('debtor');

        $year = new Year($this->getKernel());
        $voucher = new Voucher($year);

        $debtor = $this->getDebtor();
        if ($debtor->get('type') != 'invoice') {
            trigger_error('You can only state invoice from this page', E_USER_ERROR);
            exit;
        }
        $debtor->loadItem();
        $items = $debtor->item->getList();

        $smarty = new k_Template(dirname(__FILE__) . '/../templates/state/invoice.tpl.php');
        return $smarty->render($this);

    }

    function getItems()
    {
        $this->getDebtor()->loadItem();
        return $this->getDebtor()->item->getList();
    }

    function t($phrase)
    {
        return $phrase;
    }

    function postForm()
    {
        $debtor_module = $this->getKernel()->module('debtor');
        $accounting_module = $this->getKernel()->useModule('accounting');
        $product_module = $this->getKernel()->useModule('product');
        $translation = $this->getKernel()->getTranslation('debtor');

        $year = new Year($this->getKernel());
        $voucher = new Voucher($year);

        $debtor = $this->getDebtor();

        if ($debtor->get('type') != 'invoice') {
            trigger_error('You can only state invoice from this page', E_USER_ERROR);
            exit;
        }

        foreach ($_POST['state_account_id'] as $product_id => $state_account_id) {
            if (empty($state_account_id)) {
                $debtor->error->set('Mindst et produkt ved ikke hvor det skal bogf�res.');
                continue;
            }

            $product = new Product($this->getKernel(), $product_id);
            $product->getDetails()->setStateAccountId($state_account_id);
        }

        if (!$debtor->state($year, $_POST['voucher_number'], $_POST['date_state'], $translation)) {
            $debtor->error->set('Kunne ikke bogf�re posten');
        } else {
            return new k_SeeOther($this->url('../', array('flare' => 'Invoice has been stated')));
        }
        return $this->render();
    }
}