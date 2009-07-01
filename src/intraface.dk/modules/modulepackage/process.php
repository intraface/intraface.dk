<?php
/*
 * This page processes the modulepackage add action.
 */

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // When we recieve from Quickpay payment
    require('../../common.php');
    session_start();
    
    $action = Intraface_modules_modulepackage_ActionStore::restoreFromIdentifier(MDB2::singleton(DB_DSN), $_GET['action_store_identifier']);
    if(!$action) {
        throw new Exception('Unable to restore action from identifier '. $_GET['action_store_identifier']);
    }
    
    // We login to the intranet with the private key
    $adapter = new Intraface_Auth_PrivateKeyLogin(MDB2::singleton(DB_DSN), session_id(), $action->getIntranetPrivateKey());
    $weblogin = $adapter->auth();
    if (!$intranet_id = $weblogin->getActiveIntranetId()) {
        throw new Exception("Unable to log in to the intranet with public key: ".$action->getIntranetPrivateKey());
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
    
    
    $shop = new Intraface_modules_modulepackage_ShopExtension();
    $order = $shop->getOrderDetails($action->getOrderIdentifier());
    
    if(empty($order)) {
        throw new Exception('Unable to restore order from identifier '.$action->getOrderIdentifier());
    }
    
    $payment_provider = 'Ilib_Payment_Authorize_Provider_'.INTRAFACE_ONLINEPAYMENT_PROVIDER;
    $payment_authorize = new $payment_provider(INTRAFACE_ONLINEPAYMENT_MERCHANT, INTRAFACE_ONLINEPAYMENT_MD5SECRET);
    $payment_postprocess = $payment_authorize->getPostProcess($_GET, $_POST, $_SESSION, $order);
    
    
    $amount = $payment_postprocess->getAmount();

    $shop->addPaymentToOrder($action->getOrderIdentifier(), $payment_postprocess);

    if ($payment_postprocess->getPbsStatus() == '000') {
        if ($amount >= $action->getTotalPrice()) {
            if ($action->execute($kernel->intranet)) {
                // we delete the action from the store
                $action_store = new Intraface_modules_modulepackage_ActionStore($kernel->intranet->get('id'));
                $action_store->restore($_GET['action_store_identifier']);
                $action_store->delete();
    
                // TODO: do we maybe want to send an email to the customer?
    
                $access_update = new Intraface_modules_modulepackage_AccessUpdate();
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
    else {
        echo 'Payment attempt registered. Not authorized';
    }
}
elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Here we are logged in so we can use the normal way to acccess files.
    require('../../include_first.php');

    $module = $kernel->module('modulepackage');
    $module->includeFile('Manager.php');
    $module->includeFile('ShopExtension.php');
    $module->includeFile('ActionStore.php');
    $module->includeFile('AccessUpdate.php');

    // When there is no payment we get this from add_package.php
    $identifier = $_GET['action_store_identifier'];

    $action_store = new Intraface_modules_modulepackage_ActionStore($kernel->intranet->get('id'));
    $action = $action_store->restore($identifier);

    if (!is_object($action)) {
        trigger_error("Problem restoring action from identifier ".$identifier, E_USER_ERROR);
        exit;
    }

    // We make a double check
    if ($action->hasAddActionWithProduct() && $action->getTotalPrice() > 0) {
        trigger_error("The actions can not be processed without payment!", E_USER_ERROR);
        exit;
    }

    if ($action->execute($kernel->intranet)) {
        // we delete the action from the store
        $action_store->delete();

        $access_update = new Intraface_modules_modulepackage_AccessUpdate();
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