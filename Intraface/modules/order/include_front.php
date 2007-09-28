<?php
/**
 * @package Intraface_Order
 */

if($kernel->user->hasModuleAccess('debtor') AND $kernel->user->hasModuleAccess('order')) {

    $debtor_module = $kernel->useModule('debtor');
    $order_module = $kernel->useModule('order');
    $order = new Order($kernel);

    if (!$order->isFilledIn()) {
        $_advice[] = array(
            'msg' => 'you can create new orders under debtor',
            'link' => $debtor_module->getPath() . 'list.php?type=order',
            'module' => 'debtor'
        );
    }
    else {
        $db = new DB_Sql;
        $db->query("SELECT * FROM debtor WHERE type = 2 AND status = 0 AND active = 1 AND intranet_id = " . $kernel->intranet->get('id'));
        $orders = $db->numRows();
        if ($orders > 0) {
            $_attention_needed[] = array(
                'msg' => 'you have unprocessed orders',
                'link' => $debtor_module->getPath() . 'list.php?type=order',
                'module' => 'debtor'
            );
        }
    }


}

?>