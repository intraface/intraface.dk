<?php
require_once '../../common.php';

$HTTP_RAW_POST_DATA = file_get_contents('php://input');

if (isset($_GET['backend']) && $_GET['backend'] == 'xmlrpcext') {
    $encoding = 'iso-8859-1';
    $options = array(
        'prefix' => 'onlinepayment.',
        'encoding' => 'iso-8859-1',
        'backend' => 'xmlrpcext');
} else {
    $encoding = 'utf-8';
    $options = array(
        'prefix' => 'onlinepayment.',
        'encoding' => $encoding,
        'backend' => 'php');
}

$server = XML_RPC2_Server::create(new Intraface_XMLRPC_OnlinePayment_Server0100($bucket->get('Doctrine_Connection_Common'), $encoding), $options);
$server->handleCall();
