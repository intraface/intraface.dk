<?php
class Intraface_modules_debtor_Controller_Depreciation extends k_Component
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

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getModel()
    {
        return $this->context->getModel();
    }

    function getDepreciation()
    {
        $debtor_module = $this->getKernel()->module('debtor');
        $invoice_module = $this->getKernel()->useModule('invoice');
        $translation = $this->getKernel()->getTranslation('debtor');
        require_once 'Intraface/modules/invoice/Depreciation.php';
        return new Depreciation($this->getModel());
    }

    function t($phrase)
    {
        return $phrase;
    }

    function getType()
    {
        return $this->context->getType();
    }

    function postForm()
    {
        $debtor_module = $this->getKernel()->module('debtor');
        $invoice_module = $this->getKernel()->useModule('invoice');
        $translation = $this->getKernel()->getTranslation('debtor');

        $depreciation = $this->getDepreciation();
        if ($depreciation->update($_POST)) {
                if ($this->getKernel()->user->hasModuleAccess('accounting')) {
                    header('location: state_depreciation.php?for=invoice&id=' . intval($object->get("id")).'&depreciation_id='.$depreciation->get('id'));
                    exit;
                } else {
                    return new k_SeeOther($this->url('../'));
                }
        }
        return new k_SeeOther($this->url('../'));

    }

    function renderHtml()
    {
        $debtor_module = $this->getKernel()->module('debtor');
        $invoice_module = $this->getKernel()->useModule('invoice');
        $translation = $this->getKernel()->getTranslation('debtor');
            $depreciation = $this->getDepreciation();
        $smarty = new k_Template(dirname(__FILE__) . '/templates/depreciation.tpl.php');
        return $smarty->render($this);


    }
}