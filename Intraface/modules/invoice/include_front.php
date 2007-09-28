<?php
/**
 * @package Intraface_Invoice
 */
if($kernel->user->hasModuleAccess('debtor') AND $kernel->user->hasModuleAccess('invoice')) {

    $invoice_module = $kernel->useModule('invoice');
    $debtor_module = $kernel->useModule('debtor');
    $invoice = new Invoice($kernel);

    if (!$invoice->isFilledIn()) {
        $_advice[] = array(
            'msg' => 'you can create new invoices in the invoice module',
            'link' => $debtor_module->getPath(),
            'module' => 'debtor'
        );
    }
}
?>
