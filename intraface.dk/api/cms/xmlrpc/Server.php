<?php
/**
 * Bruges til at hente data via xml-rpc uden for selve intranettet.
 *
 * @author Lars Olesen <lars@legestue.net>
 */

require('../../XMLRPC/Server.php');
 
class API_CMS_XMLRPC_Server extends API_XMLRPC_Server {

	var $credentials;
	var $kernel;
	var $site;

	function __construct() {

		parent::__construct();

		$this->addCallback(
			'page.get',
			'this:getPage',
			array('array', 'struct', 'integer', 'integer'),
			'Returns an array with contents to the page. Takes three parameters, <var>struct $credentials</var>, <var>int $site_id</var> and <var>int $page_id</var>.'
		);

		$this->addCallback(
			'page.list',
			'this:getList',
			array('array', 'struct', 'integer', 'string'),
			'Returns an array with pages. Takes three parameters, <var>struct $credentials</var>, <var>int $site_id</var> and <var>string $type</var> ($type can be page, article, news).'
		);		

		$this->addCallback(
			'site.sitemap',
			'this:getSitemap',
			array('array', 'struct', 'integer'),
			'Returns an array with sitemap. Takes two parameters, <var>struct $credentials</var> and <var>int $site_id</var>.'
		);		
		
		$this->serve();
    }

	function factory($site_id) {
		if (!$this->kernel->intranet->hasModuleAccess('cms')) {
			return new IXR_Error(-2, 'Intranettet har ikke adgang til modulet cms');		
		}
		if (empty($site_id) OR !is_numeric($site_id)) {
			return new IXR_Error(-5, 'Siteid er ikke gyldigt');
		}
		$cms_module = $this->kernel->module('cms');
		$this->cmssite = new CMS_Site($this->kernel, $site_id);
	}
	
	/** 
	 * Metode til at hente en side
	 *
	 * @param struct $arg
	 * [0] $credentials
	 * [1] $id
	 */
	
	function getPage($arg) {

		if (is_object($return = $this->checkCredentials($arg[0]))) {
			return $return; 
		}
		
		// validate
		$site_id = intval($arg[1]);
		$identifier = strip_tags($arg[2]);
				
		$this->factory($site_id);

		$send_array = array(
			'identifier' => $identifier,
			'site_id' => $site_id
		);
		
		$cmspage = CMS_Page::factory($this->cmssite->kernel, 'identifier', $send_array);
		if (!isset($cmspage) OR !is_object($cmspage) OR !$cmspage->get('id') > 0) {
			// det er muligt at dette kan have fejlsideindhold
			// måske skal man kunne vælge en side til en 404 mv., som så bare hentes i stedet.
			$values['http_header_status'] = 'HTTP/1.0 404 Not Found';
			$values['content'] = 'Siden er ikke fundet';
			$values['navigation-main'] = '';
			$values['css'] = '';
			$values['css_header'] = '';
			$values['sections'] = array(); // this could be the 404
			$values['comments'] = array();

		}
		else {
			$cmspage->value['http_header_status'] = 'HTTP/1.0 200 OK';
			$cmspage->value['navigation_toplevel'] = $cmspage->navigation->build('toplevel', 'array');			
			$cmspage->value['navigation_sublevel'] = $cmspage->navigation->build('sublevel', 'array');
			$cmspage->value['sections'] = $cmspage->collect();
			$cmspage->value['comments'] = $cmspage->getComments();
			$cmspage->value['css_header'] = $cmspage->cmssite->stylesheet->get('header');
			$cmspage->value['css'] = $cmspage->cmssite->stylesheet->get('css');
			$values = $cmspage->get();
		}
		
		return $values;
	}  

	function getList($arg) {
	
		if (is_object($return = $this->checkCredentials($arg[0]))) {
			return $return; 
		}

		$site_id = $arg[1];
		$type = strip_tags($arg[2]);
		
		$this->factory($site_id);

		$cmspage = new CMS_Page($this->cmssite);
		$cmspage->dbquery->setFilter('type', $type);
		return $cmspage->getList();
	}

	function getSitemap($arg) {
		if (is_object($return = $this->checkCredentials($arg[0]))) {
			return $return; 
		}

		$site_id = $arg[1];
		
		$this->factory($site_id);
		
		$sitemap = new CMS_SiteMap($this->cmssite);
		return $sitemap->build();
	
	}
	/*
	function addMessage($arg) {
		if (is_object($return = $this->checkCredentials($arg[0]))) {
			return $return; 
		}
		
		$page_id = $arg[1];
		$values = $arg[2];
		
		$cmspage = CMS_Page::factory($kernel, 'id', $page_id);
		$message = new Message($cmspage);
		return $message->save($values);
		
	}
	*/
	
}

if($_SERVER['REQUEST_METHOD'] != 'POST' || $_SERVER['CONTENT_TYPE'] == 'application/x-www-form-urlencoded') {
	require('../../XMLRPC/Documentor.php');
	$doc = new API_XMLRPC_Documentor(PATH_WWW . $_SERVER['PHP_SELF']);
	$doc->setDescription('
		<p>You can get info from this class using XML-RPC. You need your <code>$private_key</code> and your <code>$site_id</code> to get any information.</p>
		<p>We wrote a clas which can help you to the XML-RPC-response.</p>
	
	');

	echo $doc->display();
}
else {
	$server = new API_CMS_XMLRPC_Server();
}
?>