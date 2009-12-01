<?php
class Intraface_modules_cms_Controller_Elements extends k_Component
{
    function map($name)
    {
        if (is_numeric($name)) {
            return 'Intraface_modules_cms_Controller_SectionEdit';
        } elseif ($name == 'create') {
            return 'Intraface_modules_cms_Controller_SectionEdit';
        }
    }

    function renderHtml()
    {
        return new k_SeeOther($this->url('../'));

    }

    function getKernel()
    {
        return $this->context->getKernel();
    }
}