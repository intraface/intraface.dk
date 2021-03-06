<?php
require_once '../../common.php';

XML_RPC2_Backend::setBackend('php');
$HTTP_RAW_POST_DATA = file_get_contents('php://input');

$options = array(
    'prefix' => 'shop.',
    'encoding' => 'iso-8859-1');

$server = XML_RPC2_Server::create(new Intraface_XMLRPC_Shop_Server(), $options);
$server->handleCall();
