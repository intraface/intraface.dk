<?php
class Intraface_modules_modulepackage_Controller_Payment extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function renderHtml()
    {
        $module = $this->getKernel()->module('modulepackage');
        $module->includeFile('Action.php');
        $module->includeFile('ActionStore.php');
        $module->includeFile('ShopExtension.php');

        $translation = $this->getKernel()->getTranslation('modulepackage');

        $action_store = new Intraface_modules_modulepackage_ActionStore($this->getKernel()->intranet->get('id'));
        $action = $action_store->restore($_GET['action_store_identifier']);

        if (!is_object($action)) {
            trigger_error("Problem restoring action from identifier ".$_GET['action_store_identifier'], E_USER_ERROR);
            exit;
        }

        $shop = new Intraface_modules_modulepackage_ShopExtension();
        $order = $shop->getOrderDetails($action->getOrderIdentifier());

        $data = array('modulepackagemanager' => $modulepackagemanager);
        $tpl = $this->template->create(dirname(__FILE__) . '/templates/payment');
        return $tpl->render($this, $data);
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }
}
