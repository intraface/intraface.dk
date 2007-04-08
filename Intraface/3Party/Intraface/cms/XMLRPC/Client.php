<?php
/**
 * CMS_XMLRPC_Client
 *
 * Wrapper for a XMLRPC-client to connect to the XMLRPC-server. Compliant
 * with both php4 and php5.
 *
 * Usage:
 * -----
 *
 * $client = new CMS_XMLRPC_Client(<private key>, <site_id>, <session_id>);
 * $page = $client->getPage(<identifier>);
 * var_dump($page);
 *
 * Requires the Incution XML RPC library
 * http://scripts.incutio.com/xmlrpc/
 * Make sure that you you define PATH_IXR.
 *
 * @author Lars Olesen <lars@legestue.net>
 * @version 1.0
 *
 */
 
if (!defined('PATH_IXR')) {
	define('PATH_IXR', '');
}
 
require_once(PATH_IXR . 'IXR.php');

class CMS_XMLRPC_Client {

	/**
	 * Instance of IXR_Client object
	 * @access private
	 * @var IXR_Client
	 */
	var $client;

	/**
	 * Credentials for the server
	 * @access private
	 * @var array
	 */
	var $credentials;

	/**
	 * Siteid
	 * @access private
	 * @var integer
	 */
	  
	var $site_id;
  
	/**
	 * Constructor
	 *
	 * @param $private_key  string   Provided by intraface intranet
	 * @param $site_id      integer  Provided by intraface
	 * @param $session_id   string   Session ID for the user
	 * @param $debug        boolean  true turns on xmlrpc-debugging
	 * @access public
	 */
	function CMS_XMLRPC_Client($private_key, $site_id, $session_id, $debug = false) {
		CMS_XMLRPC_Client::__construct($private_key, $site_id, $session_id, $debug);
	}

	function __construct($private_key, $site_id, $session_id, $debug = false) {

		// url for the server
		$url = 'http://www.intraface.dk/xmlrpc/cms/server.php'; 
		
		// credentials
		$this->credentials = array(
			'private_key' => $private_key, 
			'session_id' => md5($session_id)
		);
    	
		$this->site_id = (int)$site_id;

		$this->client= new IXR_Client($url);
		$this->client->debug=$debug;    
	
	}
  
	/**
	 * Returns an array with the page
	 * @param $search   string
	 * @return array
	 * @access public
	 */
	function getPage($identifier) {
		if (!$this->client->query('page.get', $this->credentials, $this->site_id, $identifier)) {
			trigger_error($this->client->getErrorCode(). ' : '.$this->client->getErrorMessage(), E_USER_ERROR);
			return false;
		}
		return $this->client->getResponse();
	}

	/**
	 * Returns an array of pages, news or articles
	 * @param $type   string  Can be page, news or article
	 * @return array
	 * @access public
	 */
	function getList($type = 'all') {
		if (!$this->client->query('page.list', $this->credentials, $this->site_id, $type)) {
			trigger_error($this->client->getErrorCode(). ' : '.$this->client->getErrorMessage(), E_USER_ERROR);
			return false;
		}
		return $this->client->getResponse();
	}

	/**
	 * Returns a sitemap
	 * @return array
	 * @access public
	 */

	function getSitemap() {
		if (!$this->client->query('site.sitemap', $this->credentials, $this->site_id)) {
			trigger_error($this->client->getErrorCode(). ' : '.$this->client->getErrorMessage(), E_USER_ERROR);
			return false;
		}
		return $this->client->getResponse();
	}	
}
?>