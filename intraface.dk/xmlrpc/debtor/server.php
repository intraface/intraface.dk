<?php
require_once '../../common.php';

XML_RPC2_Backend::setBackend('php');
$HTTP_RAW_POST_DATA = file_get_contents('php://input');
$server = XML_RPC2_Server::create(new Intraface_XMLRPC_Debtor_Server(), array('prefix' => 'debtor.'));
$server->handleCall();
