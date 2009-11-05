<?php
class Intraface_modules_newsletter_Controller_Index extends k_Component
{
    protected $registry;
    protected $page;

    function __construct(k_Registry $registry)
    {
        $this->registry = $registry;
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function map($name)
    {
        if ($name == 'lists') {
            return 'Intraface_modules_newsletter_Controller_Lists';
        }
    }

    function renderHtml()
    {
        return new k_SeeOther($this->url('lists'));
    }
}