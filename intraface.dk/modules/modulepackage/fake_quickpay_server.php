<?php
require('../../common.php');
session_start();

require_once 'Payment/Html/Provider/FakeQuickpay/PaymentProcess.php';
$server = new Payment_Html_Provider_FakeQuickpay_PaymentProcess(INTRAFACE_ONLINEPAYMENT_MD5SECRET);
$server->run($_POST);
?>