<?php
class Intraface_Controller_ModuleGatekeeper extends k_Component
{
    protected $template;
    protected $mdb2;

    function __construct(k_TemplateFactory $template, MDB2_Driver_Common $mdb2)
    {
        $this->template = $template;
        $this->mdb2 = $mdb2;
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

        $gateway = new Intraface_ModuleGateway($this->mdb2);
        return $gateway->getList();
    }
}
