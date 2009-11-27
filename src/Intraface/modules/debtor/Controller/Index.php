<?php
class Intraface_modules_debtor_Controller_Index extends k_Component
{
    function map($name)
    {
        if ($name == 'reminders') {
            return 'Intraface_modules_debtor_Controller_Reminders';
        } elseif ($name == 'settings') {
            return 'Intraface_modules_debtor_Controller_Settings';
        }
        return 'Intraface_modules_debtor_Controller_Typenegotiator';
    }

    function getRedirectUrl()
    {
        return $this->url();
    }

    function renderHtml()
    {
        if ($this->getKernel()->user->hasModuleAccess("invoice")) {
            return new k_SeeOther($this->url('invoice/list', array('type' => 'invoice')));
        } elseif ($this->getKernel()->user->hasModuleAccess("order")) {
            return new k_SeeOther($this->url('order/list', array('type' => 'order')));
        } elseif ($this->getKernel()->user->hasModulesAccess("quotation")) {
	        return new k_SeeOther($this->url('quotation/list', array('type' => 'quotation')));
        }

        $smarty = new k_Template(dirname(__FILE__) . '/templates/index.tpl.php');
        return $smarty->render($this);
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }
}