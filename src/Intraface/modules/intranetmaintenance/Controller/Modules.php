<?php
class Intraface_modules_intranetmaintenance_Controller_Modules extends k_Component
{
    protected $registry;
    protected $module_msg;

    function __construct(k_Registry $registry)
    {
        $this->registry = $registry;
    }

    function renderHtml()
    {
        $module = $this->getModule();
        $translation = $this->getKernel()->getTranslation('intranetmaintenance');

        if (isset($_GET["do"]) && $_GET["do"] == "register") {
            $this->module_msg = $this->getModuleMaintenance()->register();
            $this->getKernel()->user->clearCachedPermission(); // Sørger for at permissions bliver reloaded.
        } else {
            $this->module_msg = array();
        }

        $smarty = new k_Template(dirname(__FILE__) . '/templates/modules.tpl.php');
        return $smarty->render($this);
    }

    function getModule()
    {
        return$this->getKernel()->module("intranetmaintenance");
    }

    function getModuleMsg()
    {
        return $this->module_msg;
    }

    function getKernel()
    {
    	return $this->context->getKernel();
    }

    function t($phrase)
    {
        return $phrase;
    }

    function getModuleMaintenance()
    {
        $primary_module = $this->getKernel()->module("intranetmaintenance");
        $translation = $this->getKernel()->getTranslation("intranetmaintenance");

        return new ModuleMaintenance;
    }

    function getModules()
    {
        return $this->getModuleMaintenance()->getList();
    }

    function putForm()
    {
        // @todo should probably be created using put instead of this
    }
}