<?php
class Intraface_modules_controlpanel_Controller_Index extends k_Component
{
    protected $registry;
    protected $intranetmaintenance;

    protected function map($name)
    {
        if ($name == 'intranet') {
            return 'Intraface_modules_intranetmaintenance_Controller_Intranet_Index';
        } elseif ($name == 'user') {
            return 'Intraface_modules_intranetmaintenance_Controller_User_Index';
        } elseif ($name == 'preferences') {
            return 'Intraface_modules_controlpanel_Controller_UserPreferences';
        }
    }

    function renderHtml()
    {
        $smarty = new k_Template(dirname(__FILE__) . '/templates/index.tpl.php');
        return $smarty->render($this);
    }

    function getKernel()
    {
    	return $this->context->getKernel();
    }

    function getModules()
    {
        return $this->getKernel()->getModules();
    }

    function t($phrase)
    {
        return $phrase;
    }
}