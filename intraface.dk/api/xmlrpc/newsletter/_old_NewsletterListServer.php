<?php
/**
 * Bruges til at hente data via xml-rpc uden for selve intranettet.
 *
 * @author Lars Olesen <lars@legestue.net>
 */

require($_SERVER['DOCUMENT_ROOT'] . '/include_first.php');
require_once('3Party/IXR/IXR.php');
 
class NewsletterListServer extends IXR_Server {

	var $kernel;
	var $list;
	var $subscriber;
	var $error;

	function NewsletterListServer() {
		$this->IXR_Server(array(
			   'list.getList' => 'this:getList'
       ));
    }

	/**
	 * Tjekker om forespørgslen må foretages
	 *
	 * @param struct $credentials
	 *  - key_code = session_id
	 * @return true ved succes ellers object med fejlen
	 */
	
	function checkCredentials($credentials) {
		if (count($credentials) != 2) {
			return new IXR_Error(-2, "Der er et forkert antal argumenter i credentials");
		}
		
		if (empty($credentials['private_key'])) {
			return new IXR_Error(-2, "Du skal skrive en kode");		
		}

		$this->kernel = new Kernel('weblogin', 'private', $credentials['private_key'], $credentials['session_id']);
		
		if (!is_object($this->kernel->intranet) AND get_class($this->kernel->intranet) != 'intranet') {
			return new IXR_Error(-2, 'Du har ikke adgang til intranettet');
		}
		
		$newsletter_module = $this->kernel->module('newsletter');
	}

	function getList($arg) {
		if (count($arg) != 2) {
			return new IXR_Error(-4, 'Der er ikke det rigtige antal argumenter til getList()');
		}
		
		$credentials = $arg;

		if (is_object($return = $this->checkCredentials($credentials))) {
			return $return; 
		}
		$list = new NewsletterList($this->kernel);
		return $list->getList();
	}
	
	function getSubscriptions() {
		if (count($arg) != 3) {
			return new IXR_Error(-4, 'Der er ikke det rigtige antal argumenter til getList()');
		}
		
		$credentials = $arg[0];

		if (is_object($return = $this->checkCredentials($credentials))) {
			return $return; 
		}

		// her skal vi finde alle de nyhedsbreve den enkelte abonnerer på!		
	}
	
}

$server = new NewsletterListServer();
?>
