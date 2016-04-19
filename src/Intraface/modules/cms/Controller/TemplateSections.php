<?php
class Intraface_modules_cms_Controller_TemplateSections extends k_Component
{
    function map($name)
    {
        if (is_numeric($name)) {
             return 'Intraface_modules_cms_Controller_TemplateSectionEdit';
        } elseif ($name == 'create') {
            return 'Intraface_modules_cms_Controller_TemplateSectionEdit';
        }
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getTemplateId()
    {
        return $this->context->name();
    }
}
