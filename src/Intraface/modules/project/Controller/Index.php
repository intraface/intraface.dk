<?php
class Intraface_modules_project_Controller_Index extends k_Component
{
    protected $registry;
    protected $user_gateway;
    protected $gateway;

    function __construct(k_Registry $registry, Intraface_UserGateway $user_gateway, Intraface_Doctrine_IntranetGateway $gateway)
    {
        $this->registry = $registry;
        $this->gateway = $gateway;
        $this->user_gateway = $user_gateway;
        }

    function renderHtml()
    {
        $doctrine = $this->registry->get('doctrine');
        $this->gateway->findByIntranetId($this->user_gateway->findByUsername($this->context->identity()->user())->getActiveIntranetId());

        $shops = Doctrine::getTable('Intraface_modules_shop_Shop')->findAll();

        $data = array('shops' => $shops);

        $template = new k_Template(dirname(__FILE__) . '/tpl/index.tpl.php');
        return $template->render($this, $data);
    }

    function t($phrase)
    {
        return $phrase;
    }
}