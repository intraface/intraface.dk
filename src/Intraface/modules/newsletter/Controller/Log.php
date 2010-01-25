<?php
class Intraface_modules_newsletter_Controller_Log extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function renderHtml()
    {
        $smarty = $this->template->create(dirname(__FILE__) . '/templates/log');
        return $smarty->render($this);
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getLog()
    {
        $module = $this->getKernel()->module("newsletter");

        $log = new Intraface_modules_newsletter_SubscribersGateway;
        $list = $this->context->getList();

        return $log->getAllUnsubscribersForList($list);
    }
}