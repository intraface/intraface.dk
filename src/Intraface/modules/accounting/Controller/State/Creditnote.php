<?php
class Intraface_modules_accounting_Controller_State_Creditnote extends k_Component
{
    protected $year;
    protected $kernel;
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function map()
    {
        return 'Intraface_modules_accounting_Controller_State_SelectYear';
    }

    function getModel()
    {
        return $this->context->getModel();
    }

    function renderHtml()
    {
        $debtor_module = $this->getKernel()->module('debtor');
        $accounting_module = $this->getKernel()->useModule('accounting');
        $product_module = $this->getKernel()->useModule('product');

        $debtor = $this->getModel();

        if ($debtor->get('type') != 'credit_note') {
            throw new Exception('You can only state credit notes from this page');
        }

        if (!$this->getYear()->readyForState($this->getModel()->get('this_date'))) {
            return new k_SeeOther($this->url('selectyear'));
        }

        $smarty = $this->template->create(dirname(__FILE__) . '/../templates/state/creditnote');
        return $smarty->render($this);

    }

    function postForm()
    {
        $debtor_module = $this->getKernel()->module('debtor');
        $accounting_module = $this->getKernel()->useModule('accounting');
        $product_module = $this->getKernel()->useModule('product');

        $year = $this->getYear();
        $voucher = new Voucher($year);

            $debtor = $this->getModel();
        if ($debtor->get('type') != 'credit_note') {
            throw new Exception('You can only state credit notes from this page');
            exit;
        }
        if (!empty($_POST['state_account_id'])) {
            foreach ($_POST['state_account_id'] as $product_id => $state_account_id) {
                if (empty($state_account_id)) {
                    $debtor->error->set('Mindst et produkt ved ikke hvor det skal bogf�res.');
                    continue;
                }

                $product = new Product($this->getKernel(), $product_id);
                $product->getDetails()->setStateAccountId($state_account_id);
            }
        }

        if ($debtor->error->isError()) {
            $debtor->loadItem();
        } elseif (!$debtor->state($year, $_POST['voucher_number'], $_POST['date_state'], $this->getKernel()->getTranslation('accounting'))) {
            $debtor->error->set('Kunne ikke bogføre posten');
            $debtor->loadItem();
        } else {
            return new k_SeeOther($this->url('../'));
        }

        return $this->render();
    }

    function getItems()
    {
        $debtor = $this->getModel();
        $this->getModel()->loadItem();
        return $items = $this->getModel()->item->getList();
    }

    function getVoucher()
    {
        $voucher = new Voucher($this->getYear());
        return $voucher;
    }

    function getYear()
    {
        $accounting_module = $this->getKernel()->useModule('accounting');

        if (is_object($this->year)) {
            return $this->year;
        }
        return $this->year = new Year($this->getKernel());
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }
}
