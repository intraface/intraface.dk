<?php
require('../../include_first.php');
require('Intraface/ModulePackage.php');
require('Intraface/ModulePackage/Manager.php');
require('Intraface/ModulePackage/ShopExtension.php');
require('Intraface/ModulePackage/ActionStore.php');

/*
 * This page processes the modulepackage add action.
 */

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // When we recieve from Quickpay payment
    if(isset($_POST['ordernum']) && isset($_POST['amount'])) {
    
        $order_id = (int)$_POST['ordernum'];
        $amount = (double)$_POST['amount'];
        
        $action_store = new Intraface_ModulePackage_ActionStore($kernel->intranet->get('id'));
        $action = $action_store->restoreFromOrderId($order_id);
        
        if(!is_object($action)) {
            trigger_error("Problem restoring action from order_id ".$order_id, E_USER_ERROR);
            exit;
        }
        
        // TODO: we need to check that the onlinepayment is ok
        
        // we append the onlinepayment to the order.
        $onlinepayment = array(
            'transaction_number' => 1,
            'transaction_status' => '000',
            'amount' => number_format($action->getTotalPrice(), 2, ',', ''),
            'text' => '');
        
        $shop = new Intraface_ModulePackage_ShopExtension();
        $shop->addPaymentToOrder($action->getOrderId(), $onlinepayment);
        
        if($amount >= $action->getTotalPrice()) {
            if($action->execute($kernel->intranet)) {
                // we delete the action from the store
                $action_store->delete();
            
                // TODO: do we maybe want to send an email to the customer?
            
                // TODO: We need to run the AccessUpdate
            
                echo 'SUCCESS!';
            }
            else {
                echo 'Failure:';
                $action->error->view();
            }
        }
        else {
            
            // TODO: Here we can send an e-mail that says they still need to pay some more OR?
            echo 'Failure: Not sufficient payment';
        }  
    }
} 
elseif($_SERVER['REQUEST_METHOD'] == 'GET') {
    // When there is no payment we get this from add_package.php
    $id = (int)$_GET['action_store_id'];
    
        
    $action_store = new Intraface_ModulePackage_ActionStore($kernel->intranet->get('id'));
    $action = $action_store->restore($id);
        
    if(!is_object($action)) {
        trigger_error("Problem restoring action from order_id ".$order_id, E_USER_ERROR);
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
    
        // TODO: we need to run AccessUpdate
            
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