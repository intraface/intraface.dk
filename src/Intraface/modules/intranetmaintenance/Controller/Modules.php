<?php
class Intraface_modules_intranetmaintenance_Controller_Modules extends k_Component
{
    protected $module_msg = array();
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function renderHtml()
    {
        $module = $this->getModule();

        $smarty = $this->template->create(dirname(__FILE__) . '/templates/modules');
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

    function getModuleMaintenance()
    {
        $primary_module = $this->getKernel()->module("intranetmaintenance");

        return new Intraface_ModuleGateway(MDB2::singleton(DB_DSN));
    }

    function getModules()
    {
        return $this->getModuleMaintenance()->getList();
    }

    function putForm()
    {
        // @todo should probably be created using put instead of this
        $this->module_msg = $this->getModuleMaintenance()->registerAll();
        $this->getKernel()->user->clearCachedPermission(); // S�rger for at permissions bliver reloaded.

        return $this->render();
    }
}