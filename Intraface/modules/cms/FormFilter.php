<?php
/**
* @package SPLIB
* @version $Id: FormFilter.php,v 1.5 2003/09/18 21:47:00 harry Exp $
*/
/**
* FormFilter<br />
* Class for examining HTML tags.<br />
* Note: requires PEAR::Validate
* @access public
* @package SPLIB
*/
class FormFilter {
    /**
    * String of allowed tags
    * @access private
    * @var string
    */
    var $allowedTags = '<a><b><strong><i><em><u><h1><h2><h3><h4><img><table><tr><th><td><thead><tfoot><tbody><caption>';
    /**
    * Instance of native XML parser
    * @access private
    * @var resource
    */
    var $parser;
    /**
    * String of allowed tags
    * @access private
    * @var string
    */
    var $post = '';
    /**
    * Used to store any XML error string
    * @access private
    * @var string
    */
    var $error = '';
    /**
    * Constructs FormFilter
    * @access public
    */
    function FormFilter() {
        $this->parser = xml_parser_create();
        xml_set_object($this->parser, $this);
        xml_set_element_handler($this->parser, 'open', 'close');
        xml_set_character_data_handler($this->parser, 'data');
    }
    /**
    * Constructs FormFilter
	 *
	 * $post skal indeholde tilladte entities - 
	 * KAN BARE IKKE HELT FINDE UD AF AT INKLUDERE DEM?
	 * @see http://www.w3.org/MarkUp/html-spec/html-spec_14.html
	 * @see http://bugs.php.net/bug.php?id=15092 --> 20 jan 2004 2:51
	 *
    * @param string data to filter
    * @return string filter data
    * @access public
    */
	function filter($post_html) {
		$this->post = '';
		$post_html  = strip_tags($post_html, $this->allowedTags);
		$post_html = utf8_encode($post_html);
		
		$post  = '<?xml version="1.0"?>';
		//$post .= '<!DOCTYPE FORMFILTER [';
		//$post .= '<!ENTITY nbsp   CDATA "&#160;" -- no-break space -->';
		//$post .= '<!ENTITY copy   CDATA "&#169;" -- copyright sign -->';
		//$post .= ']>';
		$post .= '<post>'.$post_html.'</post>';
		if (!xml_parse($this->parser, $post, true)) {
			$this->error='Post data is not well formed: '.
				xml_error_string(xml_get_error_code($this->parser)). ' on line '.xml_get_current_line_number($this->parser);
			return false;
		}
		return $this->post;
	}
    /**
    * Returns any XML errors
    * @return string XML error
    * @access public
    */
    function getError() {
        return $this->error;
    }
    /**
    * Sax Open TagHandler
    * @param XML_HTMLSax
    * @param string tag name
    * @param array attributes
    * @return void
    * @access private
    */
	function open(& $parser,$tag,$attrs) {
		switch ($tag) {
			case 'A':
				if (isset($attrs['HREF']) && Validate::uri($attrs['HREF'])) {
                    $this->post .= '<a href="'.$attrs['HREF'].'" target="_blank">';
				} 
				else {
					$this->post .= '<a href="#" title="Ugyldig url">';
				}
				break;
			case 'IMG':
				if (isset($attrs['SRC']) && Validate::uri($attrs['SRC'])) {
					$this->post .= '<img src="'.$attrs['SRC'].'" />';
				} 
				else {
					$this->post .= '<img src="#" alt="Ugyldig url" />';
				}
				break;												
			case 'H1':
				// fall through
			case 'H2':
				$this->post .= '<h2>';
				break;
			case 'H3':
				$this->post .= '<h3>';
				break;
			case 'H4':
				$this->post .= '<h4>';
                break;            
			case 'B':
				// fall through
			case 'STRONG':
				$this->post .= '<strong>';
				break;
			case 'I':
				// fall through
			case 'EM':
				$this->post .= '<em>';
				break;
			case 'TABLE':
				$this->post .= '<table>';
				break;
			case 'TH':
				$this->post .= '<th>';
				break;
			case 'TR':
				$this->post .= '<tr>';
				break;												
			case 'TD':
				$this->post .= '<td>';
				break;
			case 'CAPTION':
				$this->post .= '<caption>';
				break;														
			case 'THEAD':
				$this->post .= '<thead>';
				break;														
			case 'TFOOT':
				$this->post .= '<tfoot>';
				break;														
			case 'TBODY':
				$this->post .= '<tbody>';
				break;														
																					
		}
	}
	
	/**
    * Sax Close TagHandler
    * @param XML_HTMLSax
    * @param string tag name
    * @param array attributes
    * @return void
    * @access private
    */
	function close(& $parser,$tag) {
		switch ( $tag ) {
			case 'A':
				$this->post .= '</a>';
				break;
			case 'IMG':
				break;
			case 'H1':
				// fall through
			case 'H2':
				$this->post .= '</h2>';
				break;
			case 'H3':
				$this->post .= '</h3>';
				break;            
			case 'H4':
				$this->post .= '</h4>';
				break;            
			case 'B':
				// fall through
			case 'STRONG':
				$this->post .= '</strong>';
				break;
			case 'I':
				// fall through
			case 'EM':
				$this->post .= '</em>';
				break;
			case 'TABLE':
				$this->post .= '</table>';
				break;
			case 'TH':
				$this->post .= '</th>';
				break;
			case 'TR':
				$this->post .= '</tr>';
				break;												
			case 'TD':
				$this->post .= '</td>';
				break;												
			case 'CAPTION':
				$this->post .= '</caption>';
				break;														
			case 'THEAD':
				$this->post .= '</thead>';
				break;														
			case 'TFOOT':
				$this->post .= '</tfoot>';
				break;														
			case 'TBODY':
				$this->post .= '</tbody>';
				break;														

		}
	}
	/**
    * Sax Data Handler
    * @param XML_HTMLSax
    * @param string data inside tag
    * @return void
    * @access private
    */
	function data(& $parser,$data) {
		$this->post .= $data;
	}
}
?>