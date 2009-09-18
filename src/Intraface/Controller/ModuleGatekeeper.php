<?php
class Intraface_Controller_ModuleGatekeeper extends k_Component
{
    protected $registry;

    /*
    function dispatch()
    {
        if ($this->name() == 'module') {
            throw new Exception('No module chosen');
        }

        try {
            $this->getKernel()->module($this->name());
        } catch (Exception $e) {
            throw new Exception('No access to module ' . $this->name());
        }

        return parent::dispatch();
    }
    */

    function map($name)
    {
        if ($name == 'intranetmaintenance') {
            return 'Intraface_modules_intranetmaintenance_Controller_Index';
        } elseif ($name == 'administration') {
            return 'Intraface_modules_administration_Controller_Index';
        } elseif ($name == 'controlpanel') {
            return 'Intraface_modules_controlpanel_Controller_Index';
        }
    }

    function renderHtml()
    {
        $primary_module = $this->getKernel()->module("intranetmaintenance");
        $smarty = new k_Template(dirname(__FILE__) . '/templates/modulegatekeeper.tpl.php');
        return $smarty->render($this);
    }

    function t($phrase)
    {
        return $phrase;
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function __construct(WireFactory $registry)
    {
        $this->registry = $registry;
    }

    function getUser()
    {
        return $this->context->getKernel()->user;
    }

    function getModules()
    {
        $module = new ModuleMaintenance;
        return $module->getList();
    }
}