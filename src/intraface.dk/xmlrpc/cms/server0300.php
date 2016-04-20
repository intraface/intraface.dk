<?php
/**
 * CMS-Server
 *
 * @package CMS
 * @author  Lars Olesen <lars@legestue.net>
 * @since   0.1.0
 * @version @package-version@
 */

require_once '../../common.php';

XML_RPC2_Backend::setBackend('php');
$HTTP_RAW_POST_DATA = file_get_contents('php://input');

$options = array('prefix' => 'cms.',
                 'encoding' => 'utf-8');

$server = XML_RPC2_Server::create(new Intraface_XMLRPC_CMS_Server0300(), $options);
$server->handleCall();
