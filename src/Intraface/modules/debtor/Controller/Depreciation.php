<?php
class Intraface_modules_debtor_Controller_Depreciation extends k_Component
{
    protected $template;
    protected $depreciation;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function map($name)
    {
        if ($name == 'state') {
            return 'Intraface_modules_accounting_Controller_State_Depreciation';
        }
    }

    function renderHtml()
    {
        $invoice_module = $this->getKernel()->useModule('invoice');
        $depreciation = $this->getModel();
        $smarty = $this->template->create(dirname(__FILE__) . '/templates/depreciation');
        return $smarty->render($this);
    }

    function postForm()
    {
        $invoice_module = $this->getKernel()->useModule('invoice');

        $depreciation = $this->getModel();
        if ($depreciation->update($_POST)) {
            if ($this->getKernel()->user->hasModuleAccess('accounting')) {
                return new k_SeeOther($this->url('state'));
            } else {
                return new k_SeeOther($this->url('../'));
            }
        }
        return $this->render();
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getDebtor()
    {
        return $this->context->getModel();
    }

    function getModel()
    {
        if (empty($this->depreciation)) {
            $invoice_module = $this->getKernel()->useModule('invoice');
            require_once 'Intraface/modules/invoice/Depreciation.php';
            $this->depreciation = new Depreciation($this->getDebtor(), $this->name());
        }
        
        return $this->depreciation;
            
    }

    function getType()
    {
        return $this->context->getType();
    }
}
