<?php
class Intraface_modules_cms_Controller_Sections extends k_Component
{
    protected $section_gateway;

    function map($name)
    {
        if (is_numeric($name)) {
            return 'Intraface_modules_cms_Controller_Section';
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

    function getSectionGateway()
    {
        if ($this->section_gateway) {
            return $this->section_gateway;
        }
        return $this->section_gateway = new Intraface_modules_cms_SectionGateway($this->getKernel());
    }
}