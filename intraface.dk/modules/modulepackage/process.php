<?php
/*
 * This page processes the modulepackage add action.
 */

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // When we recieve from Quickpay payment
    require('../../common.php');
    
    session_start();
    
    require_once 'Ilib/Payment/Html.php';
    $payment_postprocess = Ilib_Payment_Html::factory(INTRAFACE_ONLINEPAYMENT_PROVIDER, 'postprocess', INTRAFACE_ONLINEPAYMENT_MERCHANT);
    $payment_postprocess->set($_POST);
    $payment_postprocess->setCompareValue(array('md5secret' => INTRAFACE_ONLINEPAYMENT_MD5SECRET));
    
    if(!$payment_postprocess->validate()) {
        trigger_error('Error in the returned values from payment!', E_USER_ERROR);
        exit;
    }
    
    if($payment_postprocess->get('intranet_public_key', 'optional') == '') {
        trigger_error('A public key is needed!', E_USER_ERROR);
        exit;
    }
    
    // We login to the intranet with the public key
    $weblogin = new Weblogin;
    if(!$intranet_id = $weblogin->auth('public', $payment_postprocess->get('intranet_public_key', 'optional'))) {
        trigger_error("Unable to log in to the intranet with public key: ".$payment_postprocess->get('intranet_public_key', 'optional'), E_USER_ERROR);
        exit;
    }
        
    $kernel = new Intraface_Kernel();
    $kernel->weblogin = $weblogin;
    $kernel->intranet = new Intraface_Intranet($intranet_id);
    $kernel->setting = new Setting($kernel->intranet->get('id'));
    
    $module = $kernel->module('modulepackage');
    $module->includeFile('Manager.php');
    $module->includeFile('ShopExtension.php');
    $module->includeFile('ActionStore.php');
    $module->includeFile('AccessUpdate.php');
    
    $action_store = new Intraface_ModulePackage_ActionStore($kernel->intranet->get('id'));
    $action = $action_store->restore($payment_postprocess->get('action_store_id', 'optional'));
        
    if(!is_object($action)) {
        trigger_error("Problem restoring action from action_store_id ".$payment_postprocess->get('action_store_id', 'optional'), E_USER_ERROR);
        exit;
    }
    
    $amount = $payment_postprocess->get('amount');
        
    // we append the onlinepayment to the order.
    $onlinepayment = array(
        'transaction_number' => $payment_postprocess->get('transaction'),
        'transaction_status' => $payment_postprocess->get('qpstat'),
        'amount' => number_format($amount, 2, ',', '.'),
        'text' => '');
        
    $shop = new Intraface_ModulePackage_ShopExtension();
    $shop->addPaymentToOrder($action->getOrderId(), $onlinepayment);
        
    if($amount >= $action->getTotalPrice()) {
        if($action->execute($kernel->intranet)) {
            // we delete the action from the store
            $action_store->delete();
        
            // TODO: do we maybe want to send an email to the customer?
            
            $access_update = new Intraface_ModulePackage_AccessUpdate();
            $access_update->run($kernel->intranet->get('id'));
        
            echo 'SUCCESS!';
        }
        else {
            echo 'Failure:';
            echo $action->error->view();
        }
    }
    else {
        
        // TODO: Here we can send an e-mail that says they still need to pay some more OR?
        trigger_error('Failure: Not sufficient payment', E_USER_ERROR);
        exit;
    }
} 
elseif($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Here we are logged in so we can use the normal way to acccess files.
    require('../../include_first.php');
    
    $module = $kernel->module('modulepackage');
    $module->includeFile('Manager.php');
    $module->includeFile('ShopExtension.php');
    $module->includeFile('ActionStore.php');
    $module->includeFile('AccessUpdate.php');

    // When there is no payment we get this from add_package.php
    $id = (int)$_GET['action_store_id'];
    
    $action_store = new Intraface_ModulePackage_ActionStore($kernel->intranet->get('id'));
    $action = $action_store->restore($id);
        
    if(!is_object($action)) {
        trigger_error("Problem restoring action from order_id ".$id, E_USER_ERROR);
        exit;
    }
    
    // We make a double check
    if($action->hasAddActionWithProduct() && $action->getTotalPrice() > 0) {
        trigger_error("The actions can not be processed without payment!", E_USER_ERROR);
        exit;
    }
                
    if($action->execute($kernel->intranet)) {
        // we delete the action from the store
        $action_store->delete();
    
        $access_update = new Intraface_ModulePackage_AccessUpdate();
        $access_update->run($kernel->intranet->get('id'));
            
        header('location: index.php?status=success');
        exit;   
    }
    else {
        // TODO: we need to find a better solution for this
        echo 'Failure:';
        $action->error->view();
    }   
}

?>