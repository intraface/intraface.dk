<?php
/**
 * @package Intraface_OnlinePayment
 */

if ($kernel->user->hasModuleAccess('onlinepayment')) {

    $onlinepayment_module = $kernel->useModule('onlinepayment');
    $onlinepayment = OnlinePayment::factory($kernel);
    $onlinepayment->dbquery->setFilter('status', 2);
    if (($payments = count($onlinepayment->getList())) > 0) {
        $_attention_needed[] = array(
            'msg' => 'some online payments has not been processed',
            'link' => $onlinepayment_module->getPath(),
            'module' => $onlinepayment_module->getName()
        );
    }

}

?>