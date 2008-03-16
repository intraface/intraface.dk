<?php
require('/home/intraface/intraface.dk/config.local.php');

require_once 'Intraface/Kernel.php';
require_once 'Intraface/Intranet.php';
require_once 'Intraface/User.php';
require_once 'Intraface/Setting.php';

class ElevforeningenIntranet extends Intranet
{
    private $intranet_id = 9;

    function __construct()
    {
        parent::__construct($this->intranet_id);
    }

    function hasModuleAccess()
    {
        return true;
    }
}

class ElevforeningenUser extends User
{
    private $user_id = 2;

    function __construct()
    {
        parent::__construct($this->user_id);
    }

    function hasModuleAccess()
    {
        return true;
    }

}

$kernel = new Kernel;
$kernel->intranet = new ElevforeningenIntranet;
$kernel->user = new ElevforeningenUser;
$kernel->setting = new Setting($kernel->intranet->get('id'), $kernel->user->get('id'));
$kernel->module('debtor');
$kernel->useModule('contact');

$debtor = new Debtor($kernel, 'order');
$debtor->dbquery->setFilter('status', 0);
foreach ($debtor->getList() as $debtor) {
    if ($debtor['payment_online'] <= 0) {
        continue;
    }

    echo $debtor['id'] . ' order set as sent';
    $d = Debtor::factory($kernel, $debtor['id']);
    $d->setStatus('sent');

    echo 'creating invoice';
    $kernel->useModule('invoice');
    $invoice = new Invoice($kernel);
    $id = $invoice->create($d);

    echo 'loading invoice';
    $invoice = Debtor::factory($kernel, $id);
    $invoice->setStatus('sent');

    echo 'getting online payments';
    $onlinepayment = OnlinePayment::factory($kernel);
    $onlinepayment->dbquery->setFilter('belong_to', 'invoice');
    $onlinepayment->dbquery->setFilter('belong_to_id', $id);
    $onlinepayment->dbquery->setFilter('status', 2);

    echo 'capturing payment';
    foreach ($onlinepayment->getList() as $payment) {
        $payment = OnlinePayment::factory($kernel, 'id', $payment['id']);
        $payment->transactionAction('capture');
    }
}

?>