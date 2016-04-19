<?php
class Intraface_modules_administration_Controller_Index extends k_Component
{
    protected $intranetmaintenance;
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

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
        $smarty = $this->template->create(dirname(__FILE__) . '/templates/index');
        return $smarty->render($this);
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }
}
