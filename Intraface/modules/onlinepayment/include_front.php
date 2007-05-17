<?php
if ($kernel->user->hasModuleAccess('onlinepayment')) {

	$onlinepayment_module = $kernel->useModule('onlinepayment');
	$implemented_providers = $module->getSetting('implemented_providers');
	$onlinepayment = OnlinePayment::factory($kernel, 'provider', $implemented_providers[$kernel->setting->get('intranet', 'onlinepayment.provider_key')]);
	if (($payments = count($onlinepayment->getList())) > 0) {
		$_attention_needed[] = array(
			'msg' => 'some online payments has not been processed',
			'link' => $onlinepayment_module->getPath(),
			'module' => $onlinepayment_module->getName()
		);
	}

}

?>