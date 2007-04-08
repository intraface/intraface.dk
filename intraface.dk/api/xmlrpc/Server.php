<?php
/**
 * A base class for all our XMLRPC-implementations.
 *
 * @author Lars Olesen <lars@legestue.net>
 * @version 1.0
 */

require '/home/intraface/devel_intraface/include_first.php';
require '3Party/IXR/IXR.php';

class API_XMLRPC_Server extends IXR_IntrospectionServer {

	var $kernel;
	var $credentials;

	function __construct($private_key, $session_id) {
		$this->IXR_IntrospectionServer();
	}
	
	/**
	 * Tjekker om der er rettigheder til at logge ind i systemet.
	 * Desuden starter metoden en kernel op
	 *
	 * @param struct $credentials
	 *	- key_code 
	 *	- session_id
	 * @return true ved succes ellers object med fejlen
	 */
	
	function checkCredentials($credentials) {
		if (empty($credentials) OR count($credentials) != 2) {
			return new IXR_Error(-4, 'wrong argument count in credentials');
		}

		$private_key = strip_tags($credentials['private_key']);
		$session_id = strip_tags($credentials['session_id']);

		if (empty($private_key)) {
			return new IXR_Error(-5, 'missing private key');
		}
		if (empty($session_id)) {
			return new IXR_Error(-5, 'missing session_id');
		}
	
		$this->credentials = array(
			'private_key' => $private_key,
			'session_id' => $session_id
		);
		

		$this->kernel = new Kernel('weblogin', 'private', $this->credentials['private_key'], $this->credentials['session_id']);
		
		if (!is_object($this->kernel->intranet) AND get_class($this->kernel->intranet) != 'intranet') {
			return new IXR_Error(-2, 'access to intranet denied');
		}
	}
}

?>