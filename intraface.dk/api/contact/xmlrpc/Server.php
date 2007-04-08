<?php
/**
 * Bruges til at hente data via xml-rpc uden for selve intranettet.
 *
 * @author Lars Olesen <lars@legestue.net>
 */

require($_SERVER['DOCUMENT_ROOT'] . '/include_first.php');
require_once('3Party/IXR/IXR.php');
 
class API_Contact_XMLRPC_Server extends IXR_Server {

	var $kernel;
	var $contact;

 
	function __construct() {
		parent::__construct();

		$this->addCallback(
			'contact.get',
			'this:get',
			array('array', 'struct', 'integer'),
			''
		);

		$this->addCallback(
			'contact.factory',
			'this:factory',
			array('boolean', 'struct', 'string', 'string'),
			''
		);
		$this->addCallback(
			'contact.save',
			'this:save',
			array('boolean', 'struct', 'string', 'struct'),
			''
		);

			   /*
			   'contact.sendLoginEmail' => 'this:sendLoginEmail',
			   'contact.getKeywords' => 'this:getKeywords',
			   'contact.getConnectedKeywords' => 'this:getConnectedKeywords',
			   */
		$this->addCallback(
			'intranet.permissions',
			'this:getIntranetPermissions',
			array('boolean', 'struct'),
			''
		);	   
			   
		$this->serve();

		$contact_module = $this->kernel->module('contact');
	
	}

	/** 
	 * Metode til at tilmelde sig nyhedsbrevet
	 *
	 * @param struct $arg
	 * [0] $credentials
	 * [1] $id
	 */
	
	function get($arg) {
		if (count($arg) != 2) {
			return new IXR_Error(-4, 'Der er ikke det rigtige antal argumenter til get()');
		}

		if (is_object($return = $this->checkCredentials($arg[0]))) {
			return $return; 
		}

		$contact = new Contact($this->kernel, $arg[1]);
		if (!$contact->get('id') > 0) {
			return 0;
		}
		$contact_info = array_merge($contact->get(), $contact->address->get());
		
		if (!$contact_info) {
			return array();
		}
		
		return $contact_info;
	}  

	function factory($arg) {
		if (count($arg) != 3) {
			return new IXR_Error(-4, 'Der er ikke det rigtige antal argumenter til get()');
		}

		if (is_object($return = $this->checkCredentials($arg[0]))) {
			return $return; 
		}

		$type = $arg[1];
		$value = $arg[2];
		
		$contact = Contact::factory($this->kernel, $type, $value);
		if (!is_object($contact) OR !$contact->get('id') > 0) {
			return 0;
		}
		$contact_info = array_merge($contact->get(), $contact->address->get());
		
		if (!$contact_info) {
			return array();
		}
		
		return $contact_info;
	}  
	
	/** 
	 * Metode til at tilmelde sig nyhedsbrevet
	 *
	 * @param struct $arg
	 * [0] $credentials
	 * [1] $array med oplysninger // Husk at sende id med
	 */
	
	function save($arg) {
		if (count($arg) != 2) {
			return new IXR_Error(-4, 'Der er ikke det rigtige antal argumenter til unsubscribe()');
		}

		if (is_object($return = $this->checkCredentials($arg[0]))) {
			return $return; 
		}

		if (!is_array($arg[1])) {
			return new IXR_Error(-5, 'Det andet parameter er ikke et array');
		}

		$values = $arg[1];
		
		$contact = new Contact($this->kernel, $values['id']);
		
		if (!$contact->save($values)) {
			$contact->error->view();
			return new IXR_Error(-6, 'Du kunne ikke opdatere ' . $arg[1]);

		}
    
		return 1;
	}
	/*
	function sendLoginEmail($arg) {
		if (count($arg) != 2) {
			return new IXR_Error(-4, 'Der er ikke det rigtige antal argumenter til sendLoginEmail()');
		}

		if (is_object($return = $this->checkCredentials($arg[0]))) {
			return $return; 
		}

		if (!is_numeric($arg[1])) {
			return new IXR_Error(-5, 'Det andet parameter er ikke et numerisk');
		}

		$id = $arg[1];
		
		$contact = new Contact($this->kernel, $id);
		
		if (!is_object($contact) OR !$contact->get('id') > 0) {
			return new IXR_Error(-5, 'Kontakten fandtes ikke ' .$arg[1]);
		}
		
		if (!$contact->sendLoginEmail()) {
			return new IXR_Error(-6, 'Du kunne ikke sende email ' .$arg[1]);
		}
    
		return 1;
	}
	

	function getKeywords($arg) {
		$credentials = $arg;
		if (is_object($return = $this->checkCredentials($credentials))) {
			return $return; 
		}
		$contact = new Contact($this->kernel);
		$contact->getKeywords();

		return $contact->keywords->getAllKeywords();
	}  
	
	
	function getConnectedKeywords($arg) {
		if (count($arg) != 2) {
			return new IXR_Error(-4, 'Der er ikke det rigtige antal argumenter til get()');
		}

		if (is_object($return = $this->checkCredentials($arg[0]))) {
			return $return; 
		}

		$contact = new Contact($this->kernel, $arg[1]);
		if (!$contact->get('id') > 0) {
			return 0;
		}
		$contact->getKeywords();
		$keywords = array();
		$keywords = $contact->keywords->getConnectedKeywords();
		return $keywords;
	}  
	*/
	function getIntranetPermissions($arg) {

		if (count($arg) != 2) {
			return new IXR_Error(-4, 'Der er ikke det rigtige antal argumenter til getIntranetPermissions. Der var ' . count($arg) . ' argumenter');
		}

		if (is_object($return = $this->checkCredentials($arg))) {
			return $return; 
		}

		$permissions = array();

		if ($this->kernel->intranet->hasModuleAccess('newsletter')) {
			$permissions[] = 'newsletter';
		}

		if ($this->kernel->intranet->hasModuleAccess('todo')) {
			$permissions[] = 'todo';
		}

		if ($this->kernel->intranet->hasModuleAccess('debtor')) {
			$permissions[] = 'debtor';
		}
		
		return $permissions;
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
	$server = new API_Contact_XMLRPC_Server();
}
?>
