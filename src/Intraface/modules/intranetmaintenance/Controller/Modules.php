<?php
class Intraface_modules_intranetmaintenance_Controller_Modules extends k_Component
{
    protected $module_msg;
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function renderHtml()
    {
        $module = $this->getModule();
        $translation = $this->getKernel()->getTranslation('intranetmaintenance');

        if (isset($_GET["do"]) && $_GET["do"] == "register") {
            $this->module_msg = $this->getModuleMaintenance()->register();
            $this->getKernel()->user->clearCachedPermission(); // S�rger for at permissions bliver reloaded.
        } else {
            $this->module_msg = array();
        }

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