<?php
class Intraface_modules_modulepackage_Controller_Payment extends k_Component
{
    protected $template;
    protected $action;
    protected $action_store;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function dispatch()
    {
        $module = $this->getKernel()->module('modulepackage');
        $module->includeFile('Action.php');
        $module->includeFile('ActionStore.php');
        $module->includeFile('ShopExtension.php');

        $this->action_store = new Intraface_modules_modulepackage_ActionStore($this->getKernel()->intranet->get('id'));
        $this->action = $this->action_store->restore($this->query('action_store_identifier'));

        if (!is_object($this->action)) {
            throw new Exception("Problem restoring action from identifier ".$this->query('action_store_identifier'));
        }

        return parent::dispatch();
    }

    function renderHtml()
    {

        $shop = new Intraface_modules_modulepackage_ShopExtension();
        $order = $shop->getOrderDetails($this->action->getOrderIdentifier());

        $lang = $this->getKernel()->getTranslation()->getLang();
        $language = (isset($lang) && $lang == 'dansk') ? 'da' : 'en';

        $payment_provider = 'Ilib_Payment_Authorize_Provider_'.INTRAFACE_ONLINEPAYMENT_PROVIDER;
        $payment_authorize = new $payment_provider(INTRAFACE_ONLINEPAYMENT_MERCHANT, INTRAFACE_ONLINEPAYMENT_MD5SECRET);
        $payment_prepare = $payment_authorize->getPrepare(
            $this->action->getOrderIdentifier(),
            $order['id'],
            $order['arrears'][$order['default_currency']],
            $order['default_currency'],
            $language,
            $this->url('../', array('status' => 'success')),
            $this->url('../payment', array('action_store_identifier' => $this->action_store->getIdentifier(), 'payment_error'=>true)),
            $this->url('../process', array('action_store_identifier' => $this->action_store->getIdentifier())),
            $this->url('/payment', array('language' => $language)),
            $this->query(),
            $this->body() // this can never be set in renderHtml()
        );

        if (!strpos($payment_prepare->getAction(), '/')) {
            $form_action = $payment_prepare->getAction().'/';
        } else {
            $form_action = $payment_prepare->getAction();
        }

        $data = array(
            'shop' => $shop,
            'order' => $order,
            'action' => $this->action,
            'translation' => $this->getKernel()->getTranslation(),
            'action_store' => $this->action_store,
            'payment_prepare' => $payment_prepare,
            'form_action' => $form_action);

        $tpl = $this->template->create(dirname(__FILE__) . '/templates/payment');
        return $tpl->render($this, $data);
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }
}
