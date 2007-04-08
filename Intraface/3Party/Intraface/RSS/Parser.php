<?php
/**
 * RSS_Parser
 *
 * @todo	Validate for required elements and how they look. 
 */

class RSS_Parser {

	var $rss_array_configuration = array(
		'title' => 'title', // req - name of the channel
		'link' => 'link', // req - url to website corresponding to the channel
		'description' => 'description', // req - phrase describing the channel
		'language' => 'language', // opt - language of the channel
		'copyright' => 'copyright', // opt - copyright notice
		'docs' => 'docs', // opt - pointer to a page explaining rss - for instance http://blogs.law.harvard.edu/tech/rss
		'pubDate' => 'pubDate', // opt RFC 822
		'image' => array( // below are required if image is not empty
			'title' => '', // title of image
			'url' => '', // to the image
			'link' => '' // link to the site
		),// opt
		'item' => array(
			'title' => 'title',
			'description' => 'description',
			'pubDate' => 'pubDate', // RFC 822
			'author' => 'author',
			'link' => 'link'
		)
	);
	var $rss_array;

	/**
	 * Constructor
	 */

	function RSS_Parser($rss_array) {
		RSS_Parser::__construct($rss_array);
	}
	
	function __construct($rss_array) {
		$this->rss_array = $rss_array;
	}

	/**
	 * Function to make it possible to use another array in the parser
	 *
	 * @param $configuation_array array The array has to be similar to the example array
	 */

	function setArrayConfiguration($configuration_array) {
		$this->rss_array_configuation = $configuration_array;
	}
	

	function parse() {
		$output  = '<?xml version="1.0" encoding="iso-8859-1"?>' . "\n";
		$output .= '<rss version="2.0" xmlns:dc="http://purl.org/dc/elements/1.1/">';
		$output .= '<channel>';
		$output .= '	<title>'.$this->rss_array[$this->rss_array_configuration['title']].'</title>';
		$output .= '	<link>'.$this->rss_array[$this->rss_array_configuration['link']].'</link>';
		$output .= '	<description>'.$this->rss_array[$this->rss_array_configuration['description']].'</description>';
		if (!empty($this->rss_array[$this->rss_array_configuration['language']])) {
			$output .= '	<language>'.$this->rss_array[$this->rss_array_configuration['language']].'</language>';
		}
		if (!empty($this->rss_array[$this->rss_array_configuration['docs']])) {
			$output .= '	<docs>'.$this->rss_array[$this->rss_array_configuration['docs']].'</docs>';
		}


		if (!empty($this->rss_array['items']) AND is_array($this->rss_array['items'])) {
			foreach ($this->rss_array['items'] AS $item) {
				$output .= '	<item>';
				$output .= '		<title>' . $item[$this->rss_array_configuration['item']['title']] . '</title>';
				$output .= '		<description>' . $item[$this->rss_array_configuration['item']['description']] . '</description>';
				$output .= '		<pubDate>' . $item[$this->rss_array_configuration['item']['pubDate']] . '</pubDate>';
				$output .= '		<author>' . $item[$this->rss_array_configuration['item']['author']] . '</author>';
				$output .= '		<link>' . $item[$this->rss_array_configuration['item']['link']] . '</link>';
				$output .= '	</item>';
			} // endforeach
		
		} // endif;
		$output .= '</channel>';
		$output .= '</rss>';
		
		return $output;
	}

}
?>