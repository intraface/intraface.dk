<?php
class Intraface_Controller_ModuleGatekeeper extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

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
        $this->document->setTitle('Module gatekeeper');

        require_once 'Intraface/modules/intranetmaintenance/ModuleMaintenance.php';

        $smarty = $this->template->create(dirname(__FILE__) . '/templates/modulegatekeeper');
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