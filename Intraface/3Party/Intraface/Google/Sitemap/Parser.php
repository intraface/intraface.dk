<?php
/**
 * Google Sitemap
 *
 * Parses an array to a Google Sitemap.
 *
 * Usage:
 * -----
 *
 * require('Google_Sitemap_Parser.php');
 * $sitemap = new Google_Sitemap_Parser(
 *	array(
 *		'link' => 'http://www.legestue.net/',
 *		'changefreq' => 'Day',
 *		'priority => 10
 *	));
 * $sitemap->setArrayConfiguration(array('url' => 'link'));
 * $sitemap->setHeaders();
 * print $sitemap->parse();
 *
 * @author  Lars Olesen <lars@legestue.net>
 * @version 1.0
 */

class Google_Sitemap_Parser {

	var $sitemap_array_configuration = array(
		'url' => 'url', // req
		'changefreq' => 'changefreq', // req
		'priority' => 'priority' // req
	);
	var $sitemap_array;

	/**
	 * Constructor
	 */

	function Google_Sitemap_Parser($sitemap_array) {
		Google_Sitemap_Parser::__construct($sitemap_array);
	}
	
	function __construct($sitemap_array) {
		$this->sitemap_array = $sitemap_array;
	}

	/**
	 * Function to make it possible to use another array in the parser
	 *
	 * @param $configuation_array array The array has to be similar to the example array
	 */

	function setArrayConfiguration($configuration_array) {
		$this->sitemap_array_configuation = $configuration_array;
	}
	

	function setHeaders() {
	 	header('Content-type: application/xml; charset=UTF-8', true);
		header('Pragma: no-cache');
	}


	/**
	  * This method will write a google sitemap
	  *
	  * @param	array	$sitemap
	  * @return	string	google sitemap - including header
	  */

	function parse() {
		$output  = '<?xml version="1.0" encoding="UTF-8"?>';
		$output .= '<urlset xmlns="http://www.google.com/schemas/sitemap/0.84">';
		
		// make sure that something is outputted
		if (empty($this->sitemap_array) OR !is_array($this->sitemap_array)) {
			return $output . '</urlset>';
		}
		foreach ($this->sitemap_array AS $item) {
			$output .= '<url>';
			$output .= '	<loc>'.$item[$this->sitemap_array_configuration['url']].'</loc>';
			$output .= '	<changefreq>'.$item[$this->sitemap_array_configuration['changefreq']].'</changefreq>';
			$output .= '	<priority>'.$item[$this->sitemap_array_configuration['priority']].'</priority>';
			$output .= '</url>';						
		}
		$output .= '</urlset>';
		return $output;	
	}
	 
}
?>