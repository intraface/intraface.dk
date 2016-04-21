<?php
class Intraface_modules_accounting_Controller_State_Invoice extends k_Component
{
    protected $year;
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
        $debtor_module = $this->getKernel()->module('debtor');
        $accounting_module = $this->getKernel()->useModule('accounting');
        $product_module = $this->getKernel()->useModule('product');

        if ($this->getDebtor()->get('type') != 'invoice') {
            throw new Exception('You can only state invoice from this page');
        }

        if (!$this->getYear()->readyForState($this->getModel()->get('this_date'))) {
            return new k_SeeOther($this->url('selectyear'));
        }

        $smarty = $this->template->create(dirname(__FILE__) . '/../templates/state/invoice');
        return $smarty->render($this);
    }

    function postForm()
    {
        $debtor_module = $this->getKernel()->module('debtor');
        $accounting_module = $this->getKernel()->useModule('accounting');
        $product_module = $this->getKernel()->useModule('product');

        $year = $this->getYear();

        $debtor = $this->getDebtor();

        if ($debtor->get('type') != 'invoice') {
            throw new Exception('You can only state invoice from this page');
        }

        if (!empty($_POST['state_account_id'])) {
            foreach ($_POST['state_account_id'] as $product_id => $state_account_id) {
                if (empty($state_account_id)) {
                    $debtor->error->set('At least one product has no state account');
                    continue;
                }

                $product = new Product($this->getKernel(), $product_id);
                $product->getDetails()->setStateAccountId($state_account_id);
            }
        }

        if (!$debtor->state($year, $_POST['voucher_number'], $_POST['date_state'], $this->getKernel()->getTranslation('debtor'))) {
            $debtor->error->set('Could not state');
        } else {
            return new k_SeeOther($this->url('../', array('flare' => 'Invoice has been stated')));
        }
        return $this->render();
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getModel()
    {
        return $this->context->getDebtor();
    }

    function getDebtor()
    {
        return $this->context->getDebtor();
    }

    function getYear()
    {
        $accounting_module = $this->getKernel()->useModule('accounting');
        if (is_object($this->year)) {
            return $this->year;
        }

        $this->year = new Year($this->getKernel());
        return $this->year;
    }

    function getVoucher()
    {
        return $voucher = new Voucher($this->getYear());
    }

    function getModule()
    {
        return $accounting_module = $this->getKernel()->useModule('accounting');
    }

    function getYears()
    {
        return $this->getYear()->getList();
    }

    function getItems()
    {
        $this->getDebtor()->loadItem();
        return $this->getDebtor()->item->getList();
    }
}
