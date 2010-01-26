<?php
class Intraface_modules_debtor_Controller_Depreciation extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function map($name)
    {
        return 'Intraface_modules_accounting_Controller_State_Depreciation';
    }

    function getDebtor()
    {
        return $this->context->getDebtor();
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getModel()
    {
        return $this->context->getModel();
    }

    function getObject()
    {
        return $this->context->getObject();
    }

    function getDepreciation()
    {
        $invoice_module = $this->getKernel()->useModule('invoice');
        require_once 'Intraface/modules/invoice/Depreciation.php';
        return new Depreciation($this->getModel(), $this->name());
    }

    function getType()
    {
        return $this->context->getType();
    }

    function postForm()
    {
        $invoice_module = $this->getKernel()->useModule('invoice');

        $depreciation = $this->getDepreciation();
        if ($depreciation->update($_POST)) {
            if ($this->getKernel()->user->hasModuleAccess('accounting')) {
                return new k_SeeOther($this->url('state'));
            } else {
                return new k_SeeOther($this->url('../'));
            }
        }
        return $this->render();
    }

    function renderHtml()
    {
        $invoice_module = $this->getKernel()->useModule('invoice');
        $depreciation = $this->getDepreciation();
        $smarty = $this->template->create(dirname(__FILE__) . '/templates/depreciation');
        return $smarty->render($this);
    }
}