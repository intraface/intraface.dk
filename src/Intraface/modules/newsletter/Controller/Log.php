<?php
class Intraface_modules_newsletter_Controller_Log extends k_Component
{
    protected $registry;

    function __construct(WireFactory $registry)
    {
        $this->registry = $registry;
    }

    function renderHtml()
    {
        $smarty = new k_Template(dirname(__FILE__) . '/templates/log.tpl.php');
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

    function t($phrase)
    {
         return $phrase;
    }
}