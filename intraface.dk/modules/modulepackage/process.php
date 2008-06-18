<?php
/*
 * This page processes the modulepackage add action.
 */

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // When we recieve from Quickpay payment
    require('../../common.php');

    session_start();

    $payment_html = new Ilib_Payment_Html(INTRAFACE_ONLINEPAYMENT_PROVIDER, INTRAFACE_ONLINEPAYMENT_MERCHANT, INTRAFACE_ONLINEPAYMENT_MD5SECRET, session_id());
        
    $payment_postprocess = $payment_html->getPostProcess();
    
    
    if(!$payment_postprocess->setPaymentResponse($_POST)) {
        trigger_error('Error in the returned values from payment!', E_USER_ERROR);
        exit;
    }
    
    $optional = $payment_postprocess->getOptionalValues();
    if($optional['intranet_public_key'] == '') {
        trigger_error('A public key is needed!', E_USER_ERROR);
        exit;
    }

    // We login to the intranet with the public key
    $adapter = new Intraface_Auth_PublicKeyLogin(MDB2::singleton(DB_DSN), session_id(), $optional['intranet_public_key']);
    $weblogin = $adapter->auth();
    if(!$intranet_id = $weblogin->getActiveIntranetId()) {
        trigger_error("Unable to log in to the intranet with public key: ".$payment_postprocess->get('intranet_public_key', 'optional'), E_USER_ERROR);
        exit;
    }

    $kernel = new Intraface_Kernel();
    $kernel->weblogin = $weblogin;
    $kernel->intranet = new Intraface_Intranet($intranet_id);
    $kernel->setting = new Intraface_Setting($kernel->intranet->get('id'));

    $module = $kernel->module('modulepackage');
    $module->includeFile('Manager.php');
    $module->includeFile('ShopExtension.php');
    $module->includeFile('ActionStore.php');
    $module->includeFile('AccessUpdate.php');

    $action_store = new Intraface_ModulePackage_ActionStore($kernel->intranet->get('id'));
    $action = $action_store->restore($optional['action_store_id']);

    if(!is_object($action)) {
        trigger_error("Problem restoring action from action_store_id ".$payment_postprocess->get('action_store_id', 'optional'), E_USER_ERROR);
        exit;
    }

    $amount = $payment_postprocess->getAmount();

    // we append the onlinepayment to the order.
    $onlinepayment = array(
        'transaction_number' => $payment_postprocess->getTransactionNumber(),
        'transaction_status' => $payment_postprocess->getTransactionStatus(),
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