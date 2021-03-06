<?php
/**
 * @package Intraface_Invoice
 */
if ($kernel->user->hasModuleAccess('debtor') and $kernel->user->hasModuleAccess('invoice')) {
    $invoice_module = $kernel->useModule('invoice');
    $debtor_module = $kernel->useModule('debtor');
    $invoice = new Invoice($kernel);

    if (!$invoice->isFilledIn()) {
        $_advice[] = array(
            'msg' => 'you can create new invoices in the invoice module',
            'link' => $this->url('module/debtor/invoice/list'),
            'module' => 'debtor'
        );
    }
}
