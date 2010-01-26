<?php
/**
 * Kan kun v�lge gyldige providers
 * Svare p� sp�rgsm�l om pbsadgangen
 *
 */
class Intraface_modules_onlinepayment_Controller_ChooseProvider extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function renderHtml()
    {
        $onlinepayment_module = $this->context->getKernel()->module('onlinepayment');
    	$onlinepayment = OnlinePayment::factory($this->context->getKernel());
    	$value = $onlinepayment->getProvider();
        $smarty = $this->template->create(dirname(__FILE__) . '/templates/chooseprovider');
        return $smarty->render($this, array('value' => $value));
    }

    function postForm()
    {
        $onlinepayment_module = $this->context->getKernel()->module('onlinepayment');

    	$onlinepayment = OnlinePayment::factory($this->context->getKernel());
    	if ($onlinepayment->setProvider($_POST)) {
    		return new k_SeeOther($this->url('../'));
    	} else {
    		$value = $_POST;
    	}
        return $this->render();
    }
}