<?php
if ($kernel->user->hasModuleAccess('onlinepayment')) {

	$onlinepayment_module = $kernel->useModule('onlinepayment');
	$onlinepayment = new OnlinePayment($kernel);
	if (($payments = count($onlinepayment->getList())) > 0) {
		$_attention_needed[] = array(
			'msg' => 'some online payments has not been processed',
			'link' => $onlinepayment_module->getPath(),
			'module' => $onlinepayment_module->getName()
		);
	}

}

?>