<?php
class Intraface_Controller_ModuleGatekeeper extends k_Component
{
    function map($name)
    {
        foreach ($this->getModules() as $module) {
            if ($module['name'] == $name) {
                try {
                    $this->getKernel()->module($name);
                } catch (Exception $e) {
                    throw new Exception('No access to module ' . $name . ': ' . $e->getMessage());
                }
                return 'Intraface_modules_'.$name.'_Controller_Index';
            }

        }
        return parent::map($name);
    }

    function renderHtml()
    {
        require_once 'Intraface/modules/intranetmaintenance/ModuleMaintenance.php';

        $smarty = new k_Template(dirname(__FILE__) . '/templates/modulegatekeeper.tpl.php');
        return $smarty->render($this);
    }

    public function getKernel()
    {
        return $this->context->getKernel();
    }

    function getUser()
    {
        return $this->context->getKernel()->user;
    }

    function getModules()
    {
        require_once 'Intraface/modules/intranetmaintenance/ModuleMaintenance.php';

        $module = new ModuleMaintenance;
        return $module->getList();
    }
}