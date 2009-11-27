<?php
class Intraface_modules_debtor_Controller_Depreciations extends k_Component
{
    function map($name)
    {
        return 'Intraface_modules_debtor_Controller_Depreciation';
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
        return new Depreciation($this->getModel());
    }

    function getType()
    {
        return $this->context->getType();
    }

    function postForm()
    {
        $invoice_module = $this->getKernel()->useModule('invoice');

        $depreciation = $this->getDepreciation();
        if ($id = $depreciation->update($_POST)) {
            if ($this->getKernel()->user->hasModuleAccess('accounting')) {
                return new k_SeeOther($this->url($id . '/state'));
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
        $smarty = new k_Template(dirname(__FILE__) . '/templates/depreciation.tpl.php');
        return $smarty->render($this);
    }
}