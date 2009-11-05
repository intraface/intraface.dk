<?php
class Intraface_Controller_ModuleGatekeeper extends k_Component
{
    protected $registry;

    function __construct(k_Registry $registry)
    {
        $this->registry = $registry;
    }

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
        foreach ($this->getModules() as $module) {
            if ($module['name'] == $name);
            return 'Intraface_modules_'.$name.'_Controller_Index';
        }
        return parent::map($name);
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
        $primary_module = $this->getKernel()->module("intranetmaintenance");

        $module = new ModuleMaintenance;
        return $module->getList();
    }
}