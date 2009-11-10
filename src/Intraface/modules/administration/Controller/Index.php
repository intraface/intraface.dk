<?php
class Intraface_modules_administration_Controller_Index extends k_Component
{
    protected $registry;
    protected $intranetmaintenance;

    protected function map($name)
    {
        if ($name == 'intranet') {
            return 'Intraface_modules_administration_Controller_Intranet';
        } elseif ($name == 'user') {
            return 'Intraface_modules_intranetmaintenance_Controller_User_Index';
        } elseif ($name == 'module') {
            return 'Intraface_modules_intranetmaintenance_Controller_Modules';
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

    function t($phrase)
    {
        return $phrase;
    }
}