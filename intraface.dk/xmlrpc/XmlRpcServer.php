<?php
/**
 * I virkeligheden skal den nok sende en md5-checksum med af de submittede værdier
 * så vi kan tjekke om de er blevet ændret undervejs.
 *
 */

require_once('3Party/IXR/IXR.php');

class XmlRpcServer extends IXR_IntrospectionServer {

	var $kernel;
	var $credentials;

	function XmlRpcServer() {
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

		$this->credentials = $credentials;

		if ($count = count($credentials) != 2) {
			return new IXR_Error(-4, 'Der er et forkert antal argumenter i credentials ('.$count.')');
		}

		if (empty($credentials['private_key'])) {
			return new IXR_Error(-5, 'Du skal skrive en kode');
		}

		$this->kernel = new Kernel('weblogin');
		if (!$this->kernel->weblogin('private', $credentials['private_key'], $credentials['session_id'])) {
			return new IXR_Error(-2, 'Du har ikke adgang til intranettet');
		}

		if (!is_object($this->kernel->intranet) AND get_class($this->kernel->intranet) != 'intranet') {
			return new IXR_Error(-2, 'Du har ikke adgang til intranettet');
		}
	}
}

?>