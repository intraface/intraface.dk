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
require_once 'Intraface/XMLRPC/CMS/Server.php';

$HTTP_RAW_POST_DATA = file_get_contents('php://input');
$options = array('prefix' => 'cms.',
                 'encoding' => 'iso-8859-1');

XML_RPC2_Backend::setBackend('php');
$server = XML_RPC2_Server::create(new Intraface_XMLRPC_CMS_Server(), $options);
$server->handleCall();
?>