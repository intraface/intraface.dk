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
        $translation = $this->getKernel()->getTranslation('debtor');

        $debtor = $this->getModel();

        if ($debtor->get('type') != 'credit_note') {
            throw new Exception('You can only state credit notes from this page');
        }

        if (!$this->getYear()->readyForState($this->getModel()->get('this_date'))) {
            return new k_SeeOther($this->url('selectyear'));
        }

        $debtor->loadItem();

        $items = $debtor->item->getList();
        $value = $debtor->get();

        $smarty = $this->template->create(dirname(__FILE__) . '/../templates/state/creditnote');
        return $smarty->render($this);

    }

    function getItems()
    {
        $debtor = $this->getModel();

        $this->getModel()->loadItem();

        return $items = $this->getModel()->item->getList();
    }

    function getVoucher()
    {
        $year = new Year($this->getKernel());
        $voucher = new Voucher($year);

        return $voucher;
    }

    function postForm()
    {
        $debtor_module = $this->getKernel()->module('debtor');
        $accounting_module = $this->getKernel()->useModule('accounting');
        $product_module = $this->getKernel()->useModule('product');

        $year = new Year($this->getKernel());
        $voucher = new Voucher($year);

            $debtor = $this->getModel();
            if ($debtor->get('type') != 'credit_note') {
                trigger_error('You can only state credit notes from this page', E_USER_ERROR);
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
                $debtor->error->set('Kunne ikke bogf�re posten');
                $debtor->loadItem();
            } else {
                return new k_SeeOther($this->url('../'));
            }

        return $this->render();
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