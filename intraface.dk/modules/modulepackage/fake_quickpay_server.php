<?php
require '../../common.php';
session_start();

$server = new Ilib_Payment_Html_Provider_FakeQuickpay_PaymentProcess(INTRAFACE_ONLINEPAYMENT_MD5SECRET);
$server->run($_POST);