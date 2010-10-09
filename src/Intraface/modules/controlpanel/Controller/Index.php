<?php
class Intraface_modules_controlpanel_Controller_Index extends k_Component
{
    protected $intranetmaintenance;
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    protected function map($name)
    {
        if ($name == 'user') {
            return 'Intraface_modules_controlpanel_Controller_User';
        }
    }

    function renderHtml()
    {
        $this->getKernel()->module('controlpanel');

        $smarty = $this->template->create(dirname(__FILE__) . '/templates/index');
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
}