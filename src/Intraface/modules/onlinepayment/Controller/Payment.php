<?php
class Intraface_modules_onlinepayment_Controller_Payment extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function renderHtml()
    {
        $module = $this->context->getKernel()->module("onlinepayment");
        $translation = $this->context->getKernel()->getTranslation('onlinepayment');
        $onlinepayment = OnlinePayment::factory($this->context->getKernel(), 'id', $this->name());

        $value['dk_amount'] = $onlinepayment->get('dk_amount');

        $smarty = $this->template->create(dirname(__FILE__) . '/templates/payment');
        return $smarty->render($this, array('value' => $value, 'kernel' => $this->context->getKernel(), 'onlinepayment' => $onlinepayment));
    }

    function postForm()
    {
        $module = $this->context->getKernel()->module("onlinepayment");
        // $onlinepayment = new OnlinePayment($this->context->getKernel(), $_POST['id']);
        // $implemented_providers = $onlinepayment_module->getSetting('implemented_providers');
        // $implemented_providers[$this->context->getKernel()->setting->get('intranet', 'onlinepayment.provider_key')]
        $onlinepayment = OnlinePayment::factory($this->context->getKernel(), 'id', $this->name());


        if ($onlinepayment->update($_POST)) {
            $onlinepayment->load();
            $value['dk_amount'] = $onlinepayment->get('dk_amount');
            //header("Location: index.php?from_id=".$onlinepayment->get("id"));
            //exit;
        } else {
            $value = $_POST;
        }
        return $this->render();
    }
}
