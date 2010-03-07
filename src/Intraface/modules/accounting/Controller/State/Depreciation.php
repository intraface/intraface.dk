<?php
class Intraface_modules_accounting_Controller_State_Depreciation extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function map($name)
    {
        if($name == 'selectyear') {
            return 'Intraface_modules_accounting_Controller_State_SelectYear';
        }
    }

    function renderHtml()
    {
        $accounting_module = $this->context->getKernel()->module('accounting');
        if (!$this->getYear()->readyForState($this->getModel()->get('this_date'))) {
            return new k_SeeOther($this->url('selectyear'));
        }

        $data = array(
        	'accounting_module' => $accounting_module,
        	'voucher' => $this->getVoucher(),
        	'year' => $this->getYear(),
        	'depreciation' => $this->getModel(),
        	'object' => $this->getDebtor(),
        	'year' => $year);

        $smarty = $this->template->create(dirname(__FILE__) . '/../templates/state/depreciation');
        return $smarty->render($this, $data);
    }

    function postForm()
    {
        $debtor_module = $this->context->getKernel()->module('accounting');

        $debtor_module = $this->context->getKernel()->module('debtor');
        $accounting_module = $this->context->getKernel()->useModule('accounting');
        $this->context->getKernel()->useModule('invoice');

        if (!$this->getYear()->readyForState($this->getModel()->get('payment_date'))) {
            return new k_SeeOther($this->url('selectyear'));
        }

        $depreciation = $this->getModel();

        $this->context->getKernel()->getSetting()->set('intranet', 'depreciation.state.account', intval($_POST['state_account_id']));

        if ($depreciation->error->isError()) {
            // nothing, we continue
        } elseif (!$depreciation->state($this->getYear(), $_POST['voucher_number'], $_POST['date_state'], $_POST['state_account_id'], $this->getKernel()->getTranslation('accounting'))) {
            $depreciation->error->set('Kunne ikke bogføre posten');
        } else {
            return new k_SeeOther($this->url('../../../'));
        }
        return $this->render();
    }

    function getType()
    {
        return $this->context->context->context->getType();
    }

    function getModel()
    {
        // return $this->getDepreciation();
        return $this->context->getModel();
    }

    function getDebtor()
    {
        return $this->context->getDebtor();
    }

    function getKernel()
    {
        return $this->context->getKernel();
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
}
