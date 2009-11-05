<?php
class Intraface_modules_project_Controller_Index extends k_Component
{
    protected $registry;

    function __construct(k_Registry $registry)
    {
        $this->registry = $registry;
    }

    function renderHtml()
    {
        $doctrine = $this->registry->get('doctrine');

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