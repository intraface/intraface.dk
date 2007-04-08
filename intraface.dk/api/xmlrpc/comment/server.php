<?php

require($_SERVER['DOCUMENT_ROOT'] . '/include_first.php');
require_once('../XmlRpcServer.php');


class XMLRPC_Message_Server extends XmlRpcServer {
 
	function XMLRPC_Message_Server() {
		XMLRPC_Message_Server::__construct();
	
	}

	function __construct() {
		XmlRpcServer::XmlRpcServer();

		$this->addCallback(
			'comment.post',
			'this:post',
			array('boolean', 'struct', 'string', 'integer', 'struct'),
			'Saves a comment. Parameters are struct $credentials, string with $type (eg. cms_page), int $id (eg. cms_page id), struct $post with the comment (name, email, headline, text)'
		);

		$this->serve();
	
	}
	
	/**
	 *
	 */
	function post($arg) {
		$credentials = $arg[0];
		if (is_object($return = $this->checkCredentials($credentials))) {
			return $return; 
		}
		
		$comment_module = $this->kernel->useShared('comment');
		$types = $comment_module->getSetting('types');

		$type = $arg[1];
		$id = (int)$arg[2]; // id er håndteret her
		$array = $arg[3];

		// skal kun bruges så længe vi ikke kører utf8
		$array = array_map('utf8_decode', $array);

		
		// types		
		if (!in_array($type, $types)) {
			return new IXR_Error(-5, 'This type is not in the array $this->types. Got ' . $type);
		}
		
		if ($type == 'cms_page') {
			$this->kernel->module('cms');
			$object = CMS_Page::factory($this->kernel, 'id', $id);
		}
		else {
			return new IXR_Error(-5, 'This type failed. Got ' . $type);
		}

		
		if ($object->id == 0) {
			return new IXR_Error(-5, 'Objektet har ikke nogen id - den fik ' . $id);		
		}
		
		// måske skulle man tage arrayet og strippe det ned til de værdier, der er brug for?
		
		$comment = new Comment($object);
		
		if (!$comment->save($array)) {
			return new IXR_Error(-5, 'Could not save the comment. Error messages from the system: ' . strtolower(implode($comment->error->message, ',')));
		}
		return 1;
		
	}
	
}

if($_SERVER['REQUEST_METHOD'] != 'POST' || $_SERVER['CONTENT_TYPE'] == 'application/x-www-form-urlencoded') {
	require('../Documentor.php');
	$doc = new XMLRPC_Documentor('http://www.intraface.dk' . $_SERVER['PHP_SELF']);
	$doc->setDescription('
	');

	echo $doc->display();
}
else {
	$server = new XMLRPC_Message_Server();
}

?>