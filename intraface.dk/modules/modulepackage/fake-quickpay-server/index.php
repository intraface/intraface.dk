<?php
require '../../../common.php';
session_start();

$server = new Ilib_Payment_Html_Provider_FakeQuickpay_PaymentProcess(INTRAFACE_ONLINEPAYMENT_MD5SECRET);
if(!empty($_GET['pay'])) {
    $url = $server->process($_POST, $_SESSION);
    // echo $server->http_response_body;
    header('Location: '.$url);
    exit;
}
else {
    echo $server->getPage($_POST, $_SESSION, 'index.php?pay=go');
}
