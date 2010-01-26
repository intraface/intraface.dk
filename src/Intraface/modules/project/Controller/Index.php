<?php
class Intraface_modules_project_Controller_Index extends k_Component
{
    protected $doctrine;
    protected $user_gateway;
    protected $gateway;
    protected $template;

    function __construct(k_TemplateFactory $template, Doctrine_Connection_Common $doctrine, Intraface_UserGateway $user_gateway, Intraface_Doctrine_IntranetGateway $gateway)
    {
        $this->gateway = $gateway;
        $this->user_gateway = $user_gateway;
        $this->doctrine = $doctrine;
        $this->template = $template;
    }

    function renderHtml()
    {
        $this->gateway->findByIntranetId($this->user_gateway->findByUsername($this->context->identity()->user())->getActiveIntranetId());

        $shops = Doctrine::getTable('Intraface_modules_shop_Shop')->findAll();

        $data = array('shops' => $shops);

        $template = $this->template->create(dirname(__FILE__) . '/tpl/index');
        return $template->render($this, $data);
    }
}