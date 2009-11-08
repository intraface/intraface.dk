<?php
/**
 * Kan kun vælge gyldige providers
 * Svare på spørgsmål om pbsadgangen
 *
 */
class Intraface_modules_onlinepayment_Controller_ChooseProvider extends k_Component
{
    function renderHtml()
    {
        $onlinepayment_module = $this->context->getKernel()->module('onlinepayment');
    	$onlinepayment = OnlinePayment::factory($this->context->getKernel());
    	$value = $onlinepayment->getProvider();
        $smarty = new k_Template(dirname(__FILE__) . '/templates/chooseprovider.tpl.php');
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

    function t($phrase)
    {
        return $phrase;
    }

}