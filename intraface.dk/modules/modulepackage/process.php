<?php
/*
 * This page processes the modulepackage add action.
 */

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // When we recieve from Quickpay payment
    require('../../common.php');
    
    
    if(!isset($_POST['CUSTOM_intanet_public_key']) || $_POST['CUSTOM_intanet_public_key'] == '') {
        trigger_error('A public key is needed!', E_USER_ERROR);
        exit;
    }
    
    // We login to the intranet with the public key
    $weblogin = new Weblogin;
    if(!$intranet_id = $weblogin->auth('public', $_POST['CUSTOM_intanet_public_key'])) {
        trigger_error("Unable to log in to the intranet with public key: ".$_POST['CUSTOM_intanet_public_key'], E_USER_ERROR);
        exit;
    }
        
    $kernel = new Kernel();
    $kernel->weblogin = $weblogin;
    $kernel->intranet = new Intranet($intranet_id);
    $kernel->setting = new Setting($kernel->intranet->get('id'));
    
    $module = $kernel->module('modulepackage');
    $module->includeFile('Manager.php');
    $module->includeFile('ShopExtension.php');
    $module->includeFile('ActionStore.php');
    $module->includeFile('AccessUpdate.php');
    
    
    $id = (int)$_POST['CUSTOM_action_store_id'];
    
    $action_store = new Intraface_ModulePackage_ActionStore($kernel->intranet->get('id'));
    $action = $action_store->restore($id);
        
    if(!is_object($action)) {
        trigger_error("Problem restoring action from action_store_id ".$action_store_id, E_USER_ERROR);
        exit;
    }
    
    /**
     * amount Beløb i mindste enhed (DKK: 1 kr skrives som 100 øre)
     * time Format: yymmddhhmmss
     * ordernum Ordrenummeret på den/de vare kunden køber
     * pbsstat Statuskode returneret fra PBS.
     * qpstat Statuskode. Statuskoder.
     * qpstatmsg En tekstbesked, der uddyber fejlkoden i qpstat.
     * merchantemail Forhandler-email som transaktionen er autoriseret til.
     * merchant Forhandlernavn som transaktionen er autoriseret til.
     * currency Valuta enhed. Typer.
     * cardtype Korttype anvendt ved betalingen.
     * transaction ID på transaktionen som skal anvendes ved fx. capture.
     * md5checkV2 md5(concat(amount, time, ordernum, pbsstat, qpstat, qpstatmsg, merchantemail, merchant, currency, cardtype, transaction, md5secret)).
     */
    
    // without md5checkV2
    $payment_vars = array('amount', 'time', 'ordernum', 'pbsstat', 'qpstat', 'qpstatmsg', 'merchantemail', 'merchant', 'currency', 'cardtype', 'transaction');
    $md5_string = '';
    
    foreach($payment_vars AS $var) {
        if(!isset($_POST[$var])) {
            trigger_error("Only payment does not contain the required fields", E_USER_ERROR); 
            exit;
        }
    }
    
    $md5_string .= 'DdkjPwYjFciQw93YdkFZSjFwFkT2o0oW2kDkd';
    
    if(!isset($_POST['md5checkV2']) || $_POST['md5checkV2'] != md5($md5_string)) {
        trigger_error('Check for onlinepayment failed!', E_USER_ERROR);
        exit;
    }
    
    if($_POST['qpstat'] != '000') {
        // We try to log the error, but this could probably gives to many items in our log.
        trigger_error('The payment was not accepted', E_USER_ERROR);
        exit;
    }
    
    $amount = ($_POST['amount']/100);
        
    // we append the onlinepayment to the order.
    $onlinepayment = array(
        'transaction_number' => $_POST['transaction'],
        'transaction_status' => $_POST['qpstat'],
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
            $action->error->view();
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